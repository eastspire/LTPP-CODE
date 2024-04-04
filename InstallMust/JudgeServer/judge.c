/**
 * @file judge.c
 * @author SQS (root@ltpp.vip)
 * @details g++ -O2 -O3 judge.c -o judge -lcap -std=c++2a
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

typedef unsigned long long int ull;

/**
 * @brief 获取最大值
 */
#define __max(a, b) (((a) > (b)) ? (a) : (b))

/**
 * @brief 结果状态未更新
 */
#define LTPP_CODE_NO_INIT -2

/**
 * @brief 代码运行错误
 */
#define LTPP_CODE_ERROR -1

/**
 * @brief 服务器错误
 */
#define LTPP_SERVER_ERROR 0

/**
 * @brief 子进程成功退出
 */
#define LTPP_CHILD_SUCCESS 0

/**
 * @brief 运行正常
 */
#define LTPP_FINISH 1

/**
 * @brief 编译错误
 */
#define LTPP_COMPILER_ERROR 2

/**
 * @brief 运行超时
 */
#define LTPP_TLE 3

/**
 * @brief 运行超内存
 */
#define LTPP_MLE 4

/**
 * @brief RE错误
 */
#define LTPP_RE 5

/**
 * @brief 用户代码运行出错
 */
#define LTPP_CHILD_ERROR 6

/**
 * @brief 创建命名空间失败
 */
#define LTPP_CHILD_FailedToCreateNamespace 7

/**
 * @brief 挂载命名空间失败
 */
#define LTPP_CHILD_FailedToMountNamespace 8

/**
 * @brief 切换用户失败
 */
#define LTPP_CHILD_FailedToSwitchUsers 9

/**
 * @brief 创建隔离环境失败
 */
#define LTPP_CHILD_FailedToCreateQuarantineEnvironment 10

/**
 * @brief 重定向流失败
 */
#define LTPP_CHILD_RedirectFailure 11

/**
 * @brief 数组长度
 */
const int N = 1e5;

/**
 * @brief chroot次数
 */
const int deep_chroot = 2;

/**
 * @brief 系统LTPP用户的uid
 */
const int ltpp_uid = 1000;

/**
 * @brief 换行
 */
const char *br = "\n";

/**
 * @brief 字符串结束符
 */
char str_end_symbol = '\0';

/**
 * @brief 空字符数组
 */
char empty_str_arr[] = "\0";

/**
 * @brief 空字符串
 */
const char *empty_str = empty_str_arr;

/**
 * @brief 根目录
 */
const char *root_path = "/";

/**
 * @brief 沙箱目录
 */
const char *sandbox_path = "/home/LTPPSANDBOX";

/**
 * @brief 标准输入
 */
const char *stdin_path = "";

/**
 * @brief 标准输出
 */
const char *stdout_path = "";

/**
 * @brief 标准错误
 */
const char *stderr_path = "";

/**
 * @brief 编译时间限制
 */
const ull *compiler_time_limit = NULL;

/**
 * @brief 运行时间限制
 */
const ull *run_time_limit = NULL;

/**
 * @brief 运行内存限制
 */
const ull *run_memory_limit = NULL;

/**
 * @brief 时间限制
 */
const ull *time_limit = NULL;

/**
 * @brief 内存限制
 */
const ull *memory_limit = NULL;

/**
 * @brief 服务器异常提示信息
 */
const char *server_error_msg = "服务器异常";

/**
 * @brief 结果结构体
 * time_limit精确到MS
 * memory_limit精确到字节
 */
struct result
{
    int status;
    ull time_used;
    ull memory_used;
    const char *msg;
};

/**
 * @brief 资源限制
 */
struct rlimit rl;

/**
 * @brief 结果结构体实例
 */
struct result *res_data = (struct result *)malloc(sizeof(struct result));

/**
 * @brief 全局子进程最大运行时间
 */
ull global_time_max_limit = 0;

/**
 * @brief 是否以及输出过结果
 */
bool has_printf_res = false;

void cout();
void initResData();
void childTimeLimit();
void childMemoryUsed();
void setProcessLimit();
void stdoutToResDataMsg();
void exitDeleteAllProcess();
void lowerFilePermissions();
void *timeoutExit(void *argc);
char *jsonEncodeValue(const char *str);
char *readFile(const char *filename);
void monitor(pid_t pid, bool is_compiler);
void run(char *run_cmd[], bool is_compiler);
void split(char **arr, char *str, const char *del);
bool judgeSameString(const char *str1, const char *str2);
void runCode(char *run_cmd[], pid_t pid, bool is_compiler);
char *concatenateStrings(const char *str1, const char *str2);
void closeDup(int &newstdin, int &newstdout, int &newstderr);
void creatNamespace(int newstdin, int newstdout, int newstderr);
void joinDeepChroot(int newstdin, int newstdout, int newstderr);
void removePrivileges(int newstdin, int newstdout, int newstderr);
void updateErrorResault(int status, const char *custom_msg, const char *error_msg);

/**
 * @brief 判断字符串是否相同
 * @param str1 第一个字符串
 * @param str2 第二个字符串
 * @return true 两个字符串相同
 * @return false 两个字符串不同
 */
bool judgeSameString(const char *str1, const char *str2)
{
    const int res = strcmp(str1, str2);
    return res == 0;
}

/**
 * @brief 更新错误结果（状态仅允许改变一次）
 * @param status 状态码
 * @param custom_msg 简约消息
 * @param error_msg 错误消息
 */
void updateErrorResault(int status, const char *custom_msg, const char *error_msg)
{
    if (res_data->status != LTPP_CODE_NO_INIT && res_data->status != LTPP_FINISH)
    {
        return;
    }
    res_data->status = status;
    if (judgeSameString(error_msg, empty_str))
    {
        res_data->msg = concatenateStrings(custom_msg, error_msg);
    }
    else
    {
        const char *tmp_error_msg = concatenateStrings(br, error_msg);
        res_data->msg = concatenateStrings(custom_msg, tmp_error_msg);
    }
}

/**
 * @brief 判断文件是否为空
 * @param file_path 文件绝对路径
 * @return true 文件为空
 * @return false 文件不为空
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
 * @brief 读取文件内容
 * @param filename 文件绝对路径
 * @return char* 读取的文件内容
 */
char *readFile(const char *filename)
{
    FILE *file = fopen(filename, "r");
    if (file == NULL)
    {
        file = fopen(filename, "w+");
        if (file == NULL)
        {
            return empty_str_arr;
        }
    }
    fseek(file, 0, SEEK_END);
    long size = ftell(file);
    fseek(file, 0, SEEK_SET);
    char *buffer = (char *)malloc(size + 1);
    if (buffer == NULL)
    {
        fclose(file);
        return empty_str_arr;
    }
    size_t bytes_read = fread(buffer, 1, size, file);
    if (bytes_read != size)
    {
        fclose(file);
        return empty_str_arr;
    }
    buffer[size] = str_end_symbol;
    fclose(file);
    return buffer;
}

/**
 * @brief 字符串拼接
 * @param str1 第一个字符串
 * @param str2 第二个字符串
 * @return char* 拼接后的字符串
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
 * @brief 获取内存使用
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
 * @brief 超时退出
 * @param argc 参数
 * @return void*
 */
void *timeoutExit(void *argc)
{
    errno = 0;
    ull time_cnt = global_time_max_limit;
    while (time_cnt > 0)
    {
        --time_cnt;
        sleep(1);
    }
    childMemoryUsed();
    res_data->time_used = global_time_max_limit * 1000;
    updateErrorResault(LTPP_TLE, "用户代码运行超时", "");
    stdoutToResDataMsg();
    cout();
    exitDeleteAllProcess();
    return NULL;
}

/**
 * @brief 资源限制
 */
void setProcessLimit()
{
    rl.rlim_cur = *time_limit / 1000 + 1;
    rl.rlim_max = rl.rlim_cur + 1;
    setrlimit(RLIMIT_CPU, &rl);
    rl.rlim_cur = RLIM_INFINITY;
    rl.rlim_max = rl.rlim_cur;
    setrlimit(RLIMIT_DATA, &rl);
}

/**
 * @brief 子线程时间监控
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
 * @brief 运行代码
 * @param run_cmd 运行命令
 * @param pid 进程PID
 * @param is_compiler 是否是编译模式
 */
void runCode(char *run_cmd[], pid_t pid, bool is_compiler)
{
    errno = 0;
    int cnt = 0;
    int path_loc = 0;
    int newstdin = -1;
    int newstdout = -1;
    int newstderr = -1;
    if (!is_compiler)
    {
        while (run_cmd[cnt] != NULL)
        {
            if (strstr(run_cmd[cnt], sandbox_path) != NULL)
            {
                path_loc = cnt;
            }
            ++cnt;
        }
        --cnt;
        // 替换为沙箱内的绝对路径
        run_cmd[path_loc] = strstr(run_cmd[path_loc], sandbox_path) + strlen(sandbox_path);
    }
    newstdin = open(stdin_path, O_RDWR | O_CREAT, 0644);
    newstdout = open(stdout_path, O_RDWR | O_CREAT, 0644);
    newstderr = open(stderr_path, O_RDWR | O_CREAT, 0644);
    setProcessLimit();
    if (newstdout != -1 && newstdin != -1 && newstderr != -1)
    {
        dup2(newstdin, fileno(stdin));
        dup2(newstdout, fileno(stdout));
        dup2(newstderr, fileno(stderr));
        if (!is_compiler)
        {
            // 运行模式下需要限制权限
            // 创建命名空间
            creatNamespace(newstdin, newstdout, newstderr);
            // 创建沙箱
            joinDeepChroot(newstdin, newstdout, newstderr);
            // 移除特权Capability
            removePrivileges(newstdin, newstdout, newstderr);
            // 降低文件权限
            lowerFilePermissions();
        }
        // 运行用户代码
        if (execvp(run_cmd[0], run_cmd) == -1)
        {
            closeDup(newstdin, newstdout, newstderr);
            exit(is_compiler ? LTPP_COMPILER_ERROR : LTPP_CHILD_ERROR);
        }
        closeDup(newstdin, newstdout, newstderr);
        exit(LTPP_CHILD_SUCCESS);
    }
    exit(LTPP_CHILD_RedirectFailure);
}

/**
 * @brief 创建命名空间
 * @param newstdin 标准输入重定向
 * @param newstdout 标准输出重定向
 * @param newstderr 标准错误重定向
 */
void creatNamespace(int newstdin, int newstdout, int newstderr)
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
 * @brief 降低文件权限
 */
void lowerFilePermissions()
{
    umask(077);
}

/**
 * @brief 移除特权
 * @param newstdin 标准输入重定向
 * @param newstdout 标准输出重定向
 * @param newstderr 标准错误重定向
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
 * @brief 防止逃逸
 * @param newstdin 标准输入重定向
 * @param newstdout 标准输出重定向
 * @param newstderr 标准错误重定向
 */
void joinDeepChroot(int newstdin, int newstdout, int newstderr)
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
 * @brief 关闭重定向
 * @param newstdin 标准输入重定向
 * @param newstdout 标准输出重定向
 * @param newstderr 标准错误重定向
 */
void closeDup(int &newstdin, int &newstdout, int &newstderr)
{
    close(newstdin);
    close(newstdout);
    close(newstderr);
}

/**
 * @brief JSON序列化字符串中某一部分
 * @param str 待序列化字符串
 * @return char* 序列化后的字符串
 */
char *jsonEncodeValue(const char *str)
{
    if (judgeSameString(str, empty_str))
    {
        // 空串需要单独处理
        return empty_str_arr;
    }
    ull len = strlen(str);
    // 预留足够大的空间存储编码后的字符串
    // 乘2因为特殊字符串转义后长度为之前2倍
    // 加1因为最后需要一个终止字符
    char *encoded_str = (char *)malloc((len * 2 + 1) * sizeof(char));
    if (encoded_str == NULL)
    {
        updateErrorResault(LTPP_SERVER_ERROR, "判题机信息序列化异常", strerror(errno));
        return empty_str_arr;
    }
    char *p = encoded_str;
    for (ull i = 0; i < len; ++i)
    {
        switch (str[i])
        {
        case '"':
            *p++ = '\\';
            *p++ = '"';
            break;
        case '\\':
            *p++ = '\\';
            *p++ = '\\';
            break;
        case '\b':
            *p++ = '\\';
            *p++ = 'b';
            break;
        case '\f':
            *p++ = '\\';
            *p++ = 'f';
            break;
        case '\n':
            *p++ = '\\';
            *p++ = 'n';
            break;
        case '\r':
            *p++ = '\\';
            *p++ = 'r';
            break;
        case '\t':
            *p++ = '\\';
            *p++ = 't';
            break;
        default:
            *p++ = str[i];
            break;
        }
    }
    *p = str_end_symbol;
    return encoded_str;
}

/**
 * @brief 终止自身及其所有子进程
 */
void exitDeleteAllProcess()
{
    int res = system("pkill -TERM -P $PPID");
    exit(LTPP_FINISH);
}

/**
 * @brief 监控
 * @param pid 进程PID
 * @param is_compiler 是否是编译模式
 */
void monitor(pid_t pid, bool is_compiler)
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
    const ull memory_used = ru.ru_maxrss << 10;
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
                const char *err_msg = readFile(stderr_path);
                updateErrorResault(status, "用户代码运行出错", err_msg);
                return;
            }
            case LTPP_COMPILER_ERROR:
            {
                status = LTPP_SERVER_ERROR;
                const char *err_msg = readFile(stderr_path);
                updateErrorResault(status, "用户代码编译出错", err_msg);
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
                updateErrorResault(status, "用户代码运行超时", "");
                return;
            }
            default:
            {
                if (is_compiler)
                {
                    status = LTPP_COMPILER_ERROR;
                    const char *err_msg = readFile(stderr_path);
                    updateErrorResault(status, "用户代码编译错误", err_msg);
                }
                else
                {
                    status = LTPP_CODE_ERROR;
                    const char *err_msg = readFile(stderr_path);
                    updateErrorResault(status, "用户代码运行错误", err_msg);
                }
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
            if (res_data->time_used > *time_limit)
            {
                res_data->status = LTPP_TLE;
            }
            else if (memory_used > *memory_limit)
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
            // 子进程被其他信号终止则为超时
            // 因为只有一个线程单独处理超时
            res_data->status = LTPP_TLE;
            break;
        }
        }
    }
    else
    {
        if (res_data->time_used > *time_limit)
        {
            res_data->status = LTPP_TLE;
        }
        else if (memory_used > *memory_limit)
        {
            res_data->status = LTPP_MLE;
        }
        else if (!isFileEmpty(stderr_path))
        {
            if (is_compiler)
            {
                status = LTPP_COMPILER_ERROR;
                const char *err_msg = readFile(stderr_path);
                updateErrorResault(status, "用户代码编译错误", err_msg);
            }
            else
            {
                status = LTPP_CODE_ERROR;
                const char *err_msg = readFile(stderr_path);
                updateErrorResault(status, "用户代码运行错误", err_msg);
            }
        }
    }
}

/**
 * @brief 标准输出内容保存到结果信息
 */
void stdoutToResDataMsg()
{
    char *out_msg = readFile(stdout_path);
    res_data->msg = concatenateStrings(res_data->msg, out_msg);
}

/**
 * @brief 运行
 * @param cmd 运行命令
 * @param is_compiler 是否是编译模式
 */
void run(char *cmd[], bool is_compiler)
{
    errno = 0;
    global_time_max_limit = *time_limit / 1000 + 2;
    pid_t pid = vfork();
    if (pid < 0)
    {
        updateErrorResault(LTPP_SERVER_ERROR, "判题机创建运行子进程出错", strerror(errno));
        stdoutToResDataMsg();
        cout();
    }
    else if (pid == 0)
    {
        runCode(cmd, pid, is_compiler);
    }
    else
    {
        // 监控时间，防止CPU时间计算异常
        // 由于主进程设置了子进程的时间，所以不能放在子进程监控
        // 否则会被超时杀死，达不到监控目的
        childTimeLimit();
        monitor(pid, is_compiler);
        if (!is_compiler)
        {
            stdoutToResDataMsg();
            cout();
            exitDeleteAllProcess();
        }
    }
}

/**
 * @brief 命令行参数解析（将分割字符替换成空格）
 * @param arr 结果数组
 * @param str 待处理字符串
 * @param del 分割字符
 */
void split(char **arr, char *str, const char *del)
{
    char *s = NULL;
    s = strtok(str, del);
    int i = 0;
    while (s != NULL && i < N - 1)
    {
        arr[i++] = s;
        s = strtok(NULL, del);
    }
    arr[i] = NULL;
}

/**
 * @brief 输出结果
 */
void cout()
{
    if (has_printf_res)
    {
        return;
    }
    has_printf_res = true;
    ull length = snprintf(NULL, 0, "{\"status\":\"%d\",\"time_used\":\"%llu\",\"memory_used\":\"%llu\",\"msg\":\"%s\"}", res_data->status, res_data->time_used, res_data->memory_used, res_data->msg);
    char *json = (char *)malloc((length + 1) * sizeof(char));
    if (json == NULL)
    {
        updateErrorResault(LTPP_SERVER_ERROR, server_error_msg, strerror(errno));
    }
    res_data->msg = jsonEncodeValue(res_data->msg);
    sprintf(json, "{\"status\":\"%d\",\"time_used\":\"%llu\",\"memory_used\":\"%llu\",\"msg\":\"%s\"}", res_data->status, res_data->time_used, res_data->memory_used, res_data->msg);
    printf("%s", json);
}

/**
 * @brief 初始化结果结构体
 */
void initResData()
{
    res_data->msg = empty_str;
    res_data->status = LTPP_CODE_NO_INIT;
    res_data->time_used = 0;
    res_data->memory_used = 0;
}

/**
 * @brief 程序入口
 * 编译命令: argv[1]
 * 运行命令: argv[2]
 * 编译时间限制: argv[3]
 * 运行时间限制: argv[4]
 * 运行内存限制: argv[5]
 * 输入文件绝对路径: argv[6]
 * 输出文件绝对路径: argv[7]
 * 错误文件绝对路径: argv[8]
 * @param argc 参数数量
 * @param argv 参数数组
 * @return int 返回结果
 */
int main(int argc, char *argv[])
{
    initResData();
    char *compiler_cmd[N] = {NULL};
    char *run_cmd[N] = {NULL};
    // 解析编译命令行
    split(compiler_cmd, argv[1], "@");
    // 解析运行命令行
    split(run_cmd, argv[2], "@");
    stdin_path = argv[6];
    stdout_path = argv[7];
    stderr_path = argv[8];
    ull tmp_compiler_time_limit = atoll(argv[3]);
    ull tmp_run_time_limit = atoll(argv[4]);
    ull tmp_run_memory_limit = atoll(argv[5]);
    compiler_time_limit = &tmp_compiler_time_limit;
    run_time_limit = &tmp_run_time_limit;
    run_memory_limit = &tmp_run_memory_limit;
    memory_limit = run_memory_limit;
    // 更新时间限制为编译时间限制
    time_limit = compiler_time_limit;
    // 编译程序
    run(compiler_cmd, true);
    // 更新时间限制为运行时间限制
    time_limit = run_time_limit;
    // 运行程序
    run(run_cmd, false);
    return 0;
}