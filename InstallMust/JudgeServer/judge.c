/**
 * @file judge.c
 * @author SQS (root@ltpp.vip)
 * @details g++ -O3 judge.c -o judge -lcap
 * @version 1.0
 * @date 2024-03-21
 * @copyright Copyright (c) 2024
 */
#include <stdio.h>
#include <cerrno>
#include <errno.h>
#include <fcntl.h>
#include <sched.h>
#include <cstring>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <sys/ipc.h>
#include <pthread.h>
#include <sys/shm.h>
#include <sys/stat.h>
#include <sys/time.h>
#include <sys/user.h>
#include <sys/wait.h>
#include <sys/mount.h>
#include <sys/types.h>
#include <sys/ptrace.h>
#include <linux/sched.h>
#include <sys/resource.h>
#include <sys/capability.h>
#pragma GCC optimize(2)
#pragma GCC optimize(3)

#define LTPP_CODE_ERROR -1
#define LTPP_SERVER_ERROR 0
#define LTPP_CHILD_SUCCESS 0
#define LTPP_FINISH 1
#define LTPP_TLE 2
#define LTPP_MLE 3
#define LTPP_RE 4
#define LTPP_CHILD_ERROR 5
#define LTPP_CHILD_FailedToCreateNamespace 6
#define LTPP_CHILD_FailedToMountNamespace 7
#define LTPP_CHILD_FailedToSwitchUsers 8
#define LTPP_CHILD_FailedToCreateQuarantineEnvironment 9
#define LTPP_CHILD_RedirectFailure 10

const int N = 1e5;
const int deep_chroot = 2;
const int ltpp_uid = 1000;
// 根目录
const char *root_path = "/";
// 沙箱目录
const char *sandbox_path = "/home/LTPPSANDBOX";

/**
 * time_limit精确到MS
 * memory_limit精确到字节
 */
struct result
{
    int status;
    unsigned long long int time_used;
    unsigned long long int memory_used;
    const char *msg;    
};

struct rlimit rl;
struct result *res_data = (struct result *)malloc(sizeof(struct result));
unsigned long long int global_time_max_limit = 0;

bool has_printf_res = false;

void cout();
void childMemoryUsed();
void childTimeLimit();
void exitDeleteAllProcess();
void lowerFilePermissions();
void *timeoutExit(void *argc);
void split(char **arr, char *str, const char *del);
char *concatenateStrings(const char *str1, const char *str2);
void closeDup(int &newstdin, int &newstdout, int &newstderr);
void removePrivileges(int newstdin, int newstdout, int newstderr);
void updateErrorResault(int status, const char *custom_msg, const char *error_msg);
void creatNamespace(const char *sandbox_path, int newstdin, int newstdout, int newstderr);
void joinDeepChroot(const char *sandbox_path, int newstdin, int newstdout, int newstderr);
void setProcessLimit(const unsigned long long int time_limit, const unsigned long long int memory_limit);
void monitor(pid_t pid, const unsigned long long int time_limit, const unsigned long long int memory_limit, const char *err);
void run(char *args[], const unsigned long long int time_limit, const unsigned long long int memory_limit, const char *in, const char *out, const char *err);
void runCode(char *args[], const unsigned long long int time_limit, const unsigned long long int memory_limit, const char *in, const char *out, const char *err, pid_t pid);

/**
 * 更新错误结果
 */
void updateErrorResault(int status, const char *custom_msg, const char *error_msg)
{
    res_data->status = status;
    res_data->msg = concatenateStrings(custom_msg, error_msg);
}

/**
 * 判断文件是否为空
 */
bool isFileEmpty(const char *file_path)
{
    FILE *file = fopen(file_path, "r");
    if (file == NULL)
    {
        return false;
    }
    fseek(file, 0, SEEK_END);
    long fileSize = ftell(file);
    fclose(file);
    if (fileSize == 0)
    {
        return true;
    }
    return false;
}

/**
 * 字符串拼接
 */
char *concatenateStrings(const char *str1, const char *str2)
{
    size_t len1 = strlen(str1);
    size_t len2 = strlen(str2);
    char *result = (char *)malloc(len1 + len2 + 1);
    if (result)
    {
        strcpy(result, str1);
        strcat(result, str2);
    }
    return result;
}

/**
 * 获取内存使用
 */
void childMemoryUsed()
{
    struct rusage usage;
    if (getrusage(RUSAGE_SELF, &usage) == 0)
    {
        // 单位:KB
        res_data->memory_used = usage.ru_maxrss;
    }
}

/**
 * 超时退出
 */
void *timeoutExit(void *argc)
{
    errno = 0;
    unsigned long long int time_cnt = global_time_max_limit;
    while (time_cnt > 0)
    {
        --time_cnt;
        sleep(1);
    }
    childMemoryUsed();
    res_data->time_used = global_time_max_limit * 1000;
    updateErrorResault(LTPP_TLE, "", "");
    cout();
    exitDeleteAllProcess();
    return NULL;
}

/**
 * 资源限制
 */
void setProcessLimit(const unsigned long long int time_limit, const unsigned long long int memory_limit)
{
    rl.rlim_cur = time_limit / 1000 + 1;
    rl.rlim_max = rl.rlim_cur + 1;
    setrlimit(RLIMIT_CPU, &rl);
    rl.rlim_cur = RLIM_INFINITY;
    rl.rlim_max = rl.rlim_cur;
    setrlimit(RLIMIT_DATA, &rl);
}

/**
 * 子线程时间监控
 */
void childTimeLimit()
{
    pthread_t tid;
    if (pthread_create(&tid, NULL, timeoutExit, NULL) != 0)
    {
        updateErrorResault(LTPP_SERVER_ERROR, "子线程监控程序创建失败", strerror(errno));
        exit(LTPP_SERVER_ERROR);
    }
}

/**
 * 运行代码
 */
void runCode(char *args[], const unsigned long long int time_limit, const unsigned long long int memory_limit, const char *in, const char *out, const char *err, pid_t pid)
{
    errno = 0;
    int cnt = 0;
    int path_loc = 0;
    while (args[cnt] != NULL)
    {
        if (strstr(args[cnt], sandbox_path) != NULL)
        {
            path_loc = cnt;
        }
        ++cnt;
    }
    --cnt;
    // 替换为沙箱内的绝对路径
    args[path_loc] = strstr(args[path_loc], sandbox_path) + strlen(sandbox_path);
    int newstdin = open(in, O_RDWR | O_CREAT, 0644);
    int newstdout = open(out, O_RDWR | O_CREAT, 0644);
    int newstderr = open(err, O_RDWR | O_CREAT, 0644);
    setProcessLimit(time_limit, memory_limit);

    if (newstdout != -1 && newstdin != -1 && newstderr != -1)
    {
        dup2(newstdin, fileno(stdin));
        dup2(newstdout, fileno(stdout));
        dup2(newstderr, fileno(stderr));
        // 创建命名空间
        creatNamespace(sandbox_path, newstdin, newstdout, newstderr);
        // 创建沙箱
        joinDeepChroot(sandbox_path, newstdin, newstdout, newstderr);
        // 移除特权Capability
        removePrivileges(newstdin, newstdout, newstderr);
        // 降低文件权限
        lowerFilePermissions();
        // 运行用户代码
        if (execvp(args[0], args) == -1)
        {
            closeDup(newstdin, newstdout, newstderr);
            exit(LTPP_CHILD_ERROR);
        }
        closeDup(newstdin, newstdout, newstderr);
    }
    else
    {
        exit(LTPP_CHILD_RedirectFailure);
    }
    exit(LTPP_CHILD_SUCCESS);
}

/**
 * 创建命名空间
 */
void creatNamespace(const char *sandbox_path, int newstdin, int newstdout, int newstderr)
{
    // 创建命名空间
    if (unshare(CLONE_NEWNS) == -1)
    {
        closeDup(newstdin, newstdout, newstderr);
        exit(LTPP_CHILD_FailedToCreateNamespace);
    }
    // 挂载命名空间（设置只读，对于之前打开的流无影响）
    if (mount(sandbox_path, "/", NULL, MS_BIND | MS_RDONLY, NULL) == -1)
    {
        closeDup(newstdin, newstdout, newstderr);
        exit(LTPP_CHILD_FailedToMountNamespace);
    }
}

/**
 * 降低文件权限
 */
void lowerFilePermissions()
{
    umask(077);
}

/**
 * 移除特权
 */
void removePrivileges(int newstdin, int newstdout, int newstderr)
{
    if (setuid(ltpp_uid) == -1)
    {
        // 切换用户失败
        closeDup(newstdin, newstdout, newstderr);
        exit(LTPP_CHILD_FailedToSwitchUsers);
    }
    cap_t caps = cap_get_proc();
    cap_clear(caps);
    cap_set_proc(caps);
}

/**
 * 防止逃逸
 */
void joinDeepChroot(const char *sandbox_path, int newstdin, int newstdout, int newstderr)
{
    for (int i = 0; i < deep_chroot; ++i)
    {
        if (chroot(sandbox_path) != 0 && chdir(root_path) != 0)
        {
            // 创建隔离环境失败
            closeDup(newstdin, newstdout, newstderr);
            exit(LTPP_CHILD_FailedToCreateQuarantineEnvironment);
        }
    }
}

/**
 * 关闭重定向
 */
void closeDup(int &newstdin, int &newstdout, int &newstderr)
{
    close(newstdin);
    close(newstdout);
    close(newstderr);
}

/**
 * 终止自身及其所有子进程
 */
void exitDeleteAllProcess()
{
    int res = system("pkill -TERM -P $PPID");
    
    exit(LTPP_FINISH);
}

/**
 * 监控
 */
void monitor(pid_t pid, const unsigned long long int time_limit, const unsigned long long int memory_limit, const char *err)
{
    errno = 0;
    int status;
    struct rusage ru;
    if (wait4(pid, &status, 0, &ru) == -1)
    {
        updateErrorResault(LTPP_SERVER_ERROR, "等待子进程出错", strerror(errno));
        return;
    }
    res_data->time_used = ru.ru_utime.tv_sec * 1000 + ru.ru_utime.tv_usec / 1000 + ru.ru_stime.tv_sec * 1000 + ru.ru_stime.tv_usec / 1000;
    res_data->memory_used = ru.ru_maxrss;
    const unsigned long long int memory_used = ru.ru_maxrss << 10;
    res_data->status = LTPP_FINISH;
    if (WIFEXITED(status))
    {
        int child_exit_status = WEXITSTATUS(status);
        if (child_exit_status != LTPP_CHILD_SUCCESS)
        {
            switch (status)
            {
            case LTPP_CHILD_ERROR:
            {
                status = LTPP_SERVER_ERROR;
                updateErrorResault(status, "用户代码运行出错", "");
                return;
            }
            case LTPP_CHILD_FailedToCreateNamespace:
            {
                status = LTPP_SERVER_ERROR;
                updateErrorResault(status, "创建命名空间失败", "");
                return;
            }
            case LTPP_CHILD_FailedToCreateQuarantineEnvironment:
            {
                status = LTPP_SERVER_ERROR;
                updateErrorResault(status, "创建隔离环境失败", "");
                return;
            }
            case LTPP_CHILD_FailedToMountNamespace:
            {
                status = LTPP_SERVER_ERROR;
                updateErrorResault(status, "挂载命名空间失败", "");
                return;
            }
            case LTPP_CHILD_FailedToSwitchUsers:
            {
                status = LTPP_SERVER_ERROR;
                updateErrorResault(status, "切换用户失败", "");
                return;
            }
            case LTPP_CHILD_RedirectFailure:
            {
                status = LTPP_SERVER_ERROR;
                updateErrorResault(status, "重定向流失败", "");
                return;
            }
            case LTPP_TLE:
            {
                status = LTPP_TLE;
                res_data->time_used = global_time_max_limit * 1000;
                updateErrorResault(status, "", "");
                return;
            }
            default:
            {
                status = LTPP_CODE_ERROR;
                updateErrorResault(status, "用户代码运行错误", "");
                return;
            }
            }
        }
    }
    if (WIFSIGNALED(status))
    {
        switch (WTERMSIG(status))
        {
        case SIGSEGV:
        {
            if (res_data->time_used > time_limit)
            {
                res_data->status = LTPP_TLE;
            }
            else if (memory_used > memory_limit)
            {
                res_data->status = LTPP_MLE;
            }
            else
            {
                res_data->status = LTPP_RE;
            }
            break;
        }
        case SIGALRM:
        case SIGXCPU:
        {
            res_data->status = LTPP_TLE;
            break;
        }
        default:
        {
            // 子进程被其他信号终止则为超时，因为只有一个线程单独处理超时
            res_data->status = LTPP_TLE;
            break;
        }
        }
    }
    else
    {
        if (res_data->time_used > time_limit)
        {
            res_data->status = LTPP_TLE;
        }
        else if (memory_used > memory_limit)
        {
            res_data->status = LTPP_MLE;
        }
        else if (!isFileEmpty(err))
        {
            updateErrorResault(LTPP_CODE_ERROR, "用户代码运行错误", "");
        }
    }
}

/**
 * 运行子进程
 */
void run(char *args[], const unsigned long long int time_limit, const unsigned long long int memory_limit, const char *in, const char *out, const char *err)
{
    errno = 0;
    global_time_max_limit = time_limit / 1000 + 2;
    pid_t pid = vfork();
    // 监控时间，防止CPU时间计算异常，由于主进程设置了子进程的时间，所以不能放在子进程监控，否则会被超时杀死，达不到监控目的
    childTimeLimit();
    if (pid < 0)
    {
        updateErrorResault(LTPP_SERVER_ERROR, "判题机创建子进程出错", strerror(errno));
        cout();
    }
    else if (pid == 0)
    {
        runCode(args, time_limit, memory_limit, in, out, err, pid);
    }
    else
    {
        monitor(pid, time_limit, memory_limit, err);
        cout();
        exitDeleteAllProcess();
    }
}

/**
 * 输出
 */
void cout()
{
    if(has_printf_res){
        return;
    }
    has_printf_res = true;
    printf("{\"status\":\"%d\",\"time_used\":\"%llu\",\"memory_used\":\"%llu\",\"msg\":\"%s\"}", res_data->status, res_data->time_used, res_data->memory_used, res_data->msg);
}

/**
 * 命令行参数解析
 */
void split(char **arr, char *str, const char *del)
{
    char *s = NULL;
    s = strtok(str, del);
    while (s != NULL)
    {
        *arr++ = s;
        s = strtok(NULL, del);
    }
    *arr++ = NULL;
}

int main(int argc, char *argv[])
{
    res_data->msg = "";
    char *cmd[N];
    // 解析命令行
    split(cmd, argv[1], "@");
    // 代码或可执行文件路径 时间 内存 输入文件 输出文件 错误文件
    run(cmd, atoll(argv[2]), atoll(argv[3]), argv[4], argv[5], argv[6]);
    return 0;
}