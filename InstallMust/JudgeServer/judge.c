/**
 * @file judge.c
 * @author SQS (root@ltpp.vip)
 * @details C语言判题机
 * @version 1.0
 * @date 2023-01-05
 * @copyright Copyright (c) 2024
 * g++ -O2 -O3 judge.c -o judge -lcap -std=c++2a
 * g++ -O2 -O3 /home/LTPP/InstallMust/JudgeServer/judge.c -o /home/LTPP/InstallMust/JudgeServer/judge -lcap -std=c++2a
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
 * @brief 进程成功退出状态码
 */
#define PROCESS_EXIT_SUCCESS 0

/**
 * @brief LTPP 代码运行错误返回给LTPP的状态码
 */
#define LTPP_CODE_ERROR -1

/**
 * @brief LTPP 服务器错误返回给LTPP的状态码
 */
#define LTPP_CODE_SERVER_ERROR 0

/**
 * @brief LTPP 运行正常返回给LTPP的状态码
 */
#define LTPP_CODE_FINISH 1

/**
 * @brief LTPP 编译错误返回给LTPP的状态码
 */
#define LTPP_CODE_COMPILER_ERROR 2

/**
 * @brief LTPP 运行超时返回给LTPP的状态码
 */
#define LTPP_CODE_TLE 3

/**
 * @brief LTPP 运行超内存返回给LTPP的状态码
 */
#define LTPP_CODE_MLE 4

/**
 * @brief LTPP RE错误返回给LTPP的状态码
 */
#define LTPP_CODE_RE 5

/**
 * @brief 用户代码运行出错的状态码
 */
#define JUDGE_CODE_CHILD_ERROR 6

/**
 * @brief 创建命名空间失败的状态码
 */
#define JUDGE_CODE_CHILD_FAILED_TO_CREATE_NAMESPACE 7

/**
 * @brief 挂载命名空间失败的状态码
 */
#define JUDGE_CODE_CHILD_FAILED_TO_MOUNT_NAMESPACE 8

/**
 * @brief 切换用户失败的状态码
 */
#define JUDGE_CODE_CHILD_FAILED_TO_SWITCH_USERS 9

/**
 * @brief 创建隔离环境失败的状态码
 */
#define JUDGE_CODE_CHILD_FAILED_TO_CREATE_QUARANTINE_ENVIRONMENT 10

/**
 * @brief 重定向流失败的状态码
 */
#define JUDGE_CODE_REDIRECT_FAILURE 11

/**
 * @brief 判题机创建子线程监控程序错误的提示信息
 */
#define JUDGE_MACHINE_CREATE_MONITOR_THREAD_ERROR "判题机创建子线程监控程序错误"

/**
 * @brief 判题机序列化信息错误的提示信息
 */
#define JUDGE_MACHINE_SERIALIZATION_EXCEPTION "判题机序列化信息错误"

/**
 * @brief 判题机等待子进程错误的提示信息
 */
#define JUDGE_MACHINE_WAIT_FOR_CHILD_PROCESS_ERROR "判题机等待子进程错误"

/**
 * @brief 判题机创建命名空间错误的提示信息
 */
#define NAMESPACE_CREATION_FAILED "判题机创建命名空间错误"

/**
 * @brief 判题机创建隔离环境错误的提示信息
 */
#define JUDGE_MACHINE_CREATE_NAMESPACE_ERROR "判题机创建隔离环境错误"

/**
 * @brief 判题机挂载命名空间错误的提示信息
 */
#define JUDGE_MACHINE_MOUNT_NAMESPACE_ERROR "判题机挂载命名空间错误"

/**
 * @brief 判题机切换用户错误的提示信息
 */
#define JUDGE_MACHINE_SWITCH_USER_ERROR "判题机切换用户错误"

/**
 * @brief 判题机重定向流错误的提示信息
 */
#define JUDGE_MACHINE_STREAM_REDIRECTION_ERROR "判题机重定向流错误"

/**
 * @brief 判判题机创建子进程错误的提示信息
 */
#define JUDGE_MACHINE_CREATE_CHILD_PROCESS_ERROR "判题机创建子进程错误"

/**
 * @brief 用户代码运行错误的提示信息
 */
#define USER_CODE_EXECUTION_ERROR "用户代码运行错误"

/**
 * @brief 用户代码编译错误的提示信息
 */
#define USER_CODE_COMPILATION_ERROR "用户代码编译错误"

/**
 * @brief TLE的提示信息
 */
#define TLE "运行超时"

/**
 * @brief MLE的提示信息
 */
#define MLE "内存超限"

/**
 * @brief RE的提示信息
 */
#define RE "运行错误"

/**
 * @brief 判题机错误的提示信息
 */
#define JUDGE_MACHINE_ERROR "判题机错误"

/**
 * @brief 根目录
 */
#define ROOT_DIRECTORY "/"

/**
 * @brief 沙箱目录
 */
#define SANDBOX_DIRECTORY "/home/LTPPSANDBOX"

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
void exitProcess();
void lowerFilePermissions();
void *timeoutExit(void *argc);
char *joinStrings(char *strings[]);
char *readFile(const char *filename);
char *jsonEncodeValue(const char *str);
void monitor(pid_t pid, bool is_compiler);
void run(char *run_cmd[], bool is_compiler);
void split(char **arr, char *str, const char *del);
bool judgeSameString(const char *str1, const char *str2);
void runCode(char *run_cmd[], pid_t pid, bool is_compiler);
void updateErrorResault(int status, const char *custom_msg);
void closeDup(int &newstdin, int &newstdout, int &newstderr);
char *concatenateStrings(const char *str1, const char *str2);
void creatNamespace(int newstdin, int newstdout, int newstderr);
void joinDeepChroot(int newstdin, int newstdout, int newstderr);
void removePrivileges(int newstdin, int newstdout, int newstderr);

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
 */
void updateErrorResault(int status, const char *custom_msg)
{
    if (res_data->status != LTPP_CODE_FINISH)
    {
        return;
    }
    res_data->status = status;
    res_data->msg = custom_msg;
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
 * @brief 获取内存使用（单位:KB）
 */
void childMemoryUsed()
{
    struct rusage usage;
    if (getrusage(RUSAGE_SELF, &usage) == 0)
    {
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
    updateErrorResault(LTPP_CODE_TLE, TLE);
    cout();
    exitProcess();
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
        updateErrorResault(LTPP_CODE_SERVER_ERROR, JUDGE_MACHINE_CREATE_MONITOR_THREAD_ERROR);
        exit(LTPP_CODE_SERVER_ERROR);
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
            if (strstr(run_cmd[cnt], SANDBOX_DIRECTORY) != NULL)
            {
                path_loc = cnt;
            }
            ++cnt;
        }
        --cnt;
        // 替换为沙箱内的绝对路径
        run_cmd[path_loc] = strstr(run_cmd[path_loc], SANDBOX_DIRECTORY) + strlen(SANDBOX_DIRECTORY);
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
            exit(is_compiler ? LTPP_CODE_COMPILER_ERROR : JUDGE_CODE_CHILD_ERROR);
        }
        closeDup(newstdin, newstdout, newstderr);
        exit(PROCESS_EXIT_SUCCESS);
    }
    exit(JUDGE_CODE_REDIRECT_FAILURE);
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
        exit(JUDGE_CODE_CHILD_FAILED_TO_CREATE_NAMESPACE);
    }
    // 挂载命名空间（设置只读，对于之前打开的流无影响）
    if (mount(SANDBOX_DIRECTORY, "/", NULL, MS_BIND | MS_RDONLY, NULL) == -1)
    {
        closeDup(newstdin, newstdout, newstderr);
        exit(JUDGE_CODE_CHILD_FAILED_TO_MOUNT_NAMESPACE);
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
        exit(JUDGE_CODE_CHILD_FAILED_TO_SWITCH_USERS);
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
        if (chroot(SANDBOX_DIRECTORY) != 0 && chdir(ROOT_DIRECTORY) != 0)
        {
            // 创建隔离环境失败
            closeDup(newstdin, newstdout, newstderr);
            exit(JUDGE_CODE_CHILD_FAILED_TO_CREATE_QUARANTINE_ENVIRONMENT);
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
        updateErrorResault(LTPP_CODE_SERVER_ERROR, JUDGE_MACHINE_SERIALIZATION_EXCEPTION);
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
 * @brief 退出
 */
void exitProcess()
{
    exit(PROCESS_EXIT_SUCCESS);
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
        updateErrorResault(LTPP_CODE_SERVER_ERROR, JUDGE_MACHINE_WAIT_FOR_CHILD_PROCESS_ERROR);
        return;
    }
    res_data->time_used = ru.ru_utime.tv_sec * 1000 + ru.ru_utime.tv_usec / 1000 + ru.ru_stime.tv_sec * 1000 + ru.ru_stime.tv_usec / 1000;
    res_data->memory_used = ru.ru_maxrss;
    const ull memory_used = ru.ru_maxrss << 10;
    res_data->status = LTPP_CODE_FINISH;
    if (WIFEXITED(status))
    {
        int child_exit_status = WEXITSTATUS(status);
        if (child_exit_status != PROCESS_EXIT_SUCCESS)
        {
            switch (status)
            {
            case JUDGE_CODE_CHILD_FAILED_TO_CREATE_NAMESPACE:
            {
                updateErrorResault(LTPP_CODE_SERVER_ERROR, NAMESPACE_CREATION_FAILED);
                break;
            }
            case JUDGE_CODE_CHILD_FAILED_TO_CREATE_QUARANTINE_ENVIRONMENT:
            {
                updateErrorResault(LTPP_CODE_SERVER_ERROR, JUDGE_MACHINE_CREATE_NAMESPACE_ERROR);
                break;
            }
            case JUDGE_CODE_CHILD_FAILED_TO_MOUNT_NAMESPACE:
            {
                updateErrorResault(LTPP_CODE_SERVER_ERROR, JUDGE_MACHINE_MOUNT_NAMESPACE_ERROR);
                break;
            }
            case JUDGE_CODE_CHILD_FAILED_TO_SWITCH_USERS:
            {
                updateErrorResault(LTPP_CODE_SERVER_ERROR, JUDGE_MACHINE_SWITCH_USER_ERROR);
                break;
            }
            case JUDGE_CODE_REDIRECT_FAILURE:
            {
                updateErrorResault(LTPP_CODE_SERVER_ERROR, JUDGE_MACHINE_STREAM_REDIRECTION_ERROR);
                break;
            }
            case LTPP_CODE_MLE:
            {
                updateErrorResault(LTPP_CODE_MLE, MLE);
                break;
            }
            case LTPP_CODE_RE:
            {
                updateErrorResault(LTPP_CODE_RE, RE);
                break;
            }
            case LTPP_CODE_TLE:
            {
                updateErrorResault(LTPP_CODE_TLE, TLE);
                break;
            }
            default:
            {
                if (is_compiler)
                {
                    updateErrorResault(LTPP_CODE_COMPILER_ERROR, USER_CODE_COMPILATION_ERROR);
                }
                else
                {
                    updateErrorResault(LTPP_CODE_ERROR, USER_CODE_EXECUTION_ERROR);
                }
                break;
            }
            }
            return;
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
                updateErrorResault(LTPP_CODE_TLE, TLE);
            }
            else if (memory_used > *memory_limit)
            {
                updateErrorResault(LTPP_CODE_MLE, MLE);
            }
            else
            {
                updateErrorResault(LTPP_CODE_MLE, RE);
            }
            break;
        }
        case SIGALRM:
        case SIGXCPU:
        {
            updateErrorResault(LTPP_CODE_TLE, TLE);
            break;
        }
        default:
        {
            // 子进程被其他信号终止则为超时
            // 因为只有一个线程单独处理超时
            updateErrorResault(LTPP_CODE_TLE, TLE);
            break;
        }
        }
    }
    else
    {
        if (res_data->time_used > *time_limit)
        {
            updateErrorResault(LTPP_CODE_TLE, TLE);
        }
        else if (memory_used > *memory_limit)
        {
            updateErrorResault(LTPP_CODE_MLE, MLE);
        }
        else if (!isFileEmpty(stderr_path))
        {
            if (is_compiler)
            {
                status = LTPP_CODE_COMPILER_ERROR;
                updateErrorResault(status, USER_CODE_COMPILATION_ERROR);
            }
            else
            {
                status = LTPP_CODE_ERROR;
                updateErrorResault(status, USER_CODE_EXECUTION_ERROR);
            }
        }
    }
}

/**
 * @brief 标准输出内容保存到结果信息
 */
void stdoutToResDataMsg()
{
    switch (res_data->status)
    {
    case LTPP_CODE_FINISH:
    case LTPP_CODE_TLE:
    case LTPP_CODE_MLE:
    case LTPP_CODE_RE:
    {
        char *out_msg = readFile(stdout_path);
        if (!judgeSameString(out_msg, empty_str))
        {
            const char *tmp_out_msg = concatenateStrings(br, out_msg);
            res_data->msg = concatenateStrings(res_data->msg, tmp_out_msg);
        }
        return;
    }
    default:
    {
        char *err_msg = readFile(stderr_path);
        if (!judgeSameString(err_msg, empty_str))
        {
            const char *tmp_err_msg = concatenateStrings(br, err_msg);
            res_data->msg = concatenateStrings(res_data->msg, tmp_err_msg);
        }
        return;
    }
    }
}

/**
 * @brief 指针数组转字符串
 * @param strings 指针数组
 * @return char* 字符串
 */
char *joinStrings(char *strings[])
{
    int num_strings = 0;
    while (strings[num_strings] != NULL)
    {
        num_strings++;
    }

    // 计算拼接后的字符串总长度
    int total_length = 0;
    for (int i = 0; i < num_strings; i++)
    {
        total_length += strlen(strings[i]);
    }
    // 分配内存用于存储拼接后的字符串
    // +1 是为了存储字符串结束符
    char *result = (char *)malloc(total_length + 1);
    if (result == NULL)
    {
        return result;
    }

    int index = 0;
    for (int i = 0; i < num_strings; i++)
    {
        strcpy(result + index, strings[i]);
        index += strlen(strings[i]);
    }

    result[index] = str_end_symbol;

    return result;
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
    const char *cmd_str = joinStrings(cmd);
    if (cmd_str == NULL || judgeSameString(cmd_str, empty_str))
    {
        // 命令为NULL或者空串则执行以下程序
        if (!is_compiler)
        {
            // 运行模式下需要输出结果
            cout();
        }
        // 无运行命令情况下直接返回
        return;
    }
    pid_t pid = vfork();
    if (pid < 0)
    {
        updateErrorResault(LTPP_CODE_SERVER_ERROR, JUDGE_MACHINE_CREATE_CHILD_PROCESS_ERROR);
        cout();
        exitProcess();
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
            cout();
            exitProcess();
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
    stdoutToResDataMsg();
    has_printf_res = true;
    ull length = snprintf(NULL, 0, "{\"status\":\"%d\",\"time_used\":\"%llu\",\"memory_used\":\"%llu\",\"msg\":\"%s\"}", res_data->status, res_data->time_used, res_data->memory_used, res_data->msg);
    char *json = (char *)malloc((length + 1) * sizeof(char));
    if (json == NULL)
    {
        updateErrorResault(LTPP_CODE_SERVER_ERROR, JUDGE_MACHINE_ERROR);
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
    res_data->status = LTPP_CODE_FINISH;
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