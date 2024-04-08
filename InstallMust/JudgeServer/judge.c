/**
 * @file judge.c
 * @author SQS (root@ltpp.vip)
 * @details C语言判题机
 * @version 1.0
 * @date 2023-01-05
 * @copyright Copyright (c) 2024
 * g++ -O2 -O3 -o judge judge.c -lcap -std=c++2a
 * g++ -O2 -O3 -o /home/LTPP/InstallMust/JudgeServer/judge /home/LTPP/InstallMust/JudgeServer/judge.c -lcap -std=c++2a
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
 * @brief 休眠微秒数
 */
#define SLEEP_MICROSECONDS 1000

/**
 * @brief 超时后最小额外等待秒数
 */
#define MIN_EXTRA_WAIT_SECONDS 1

/**
 * @brief 超时后最大额外等待秒数
 */
#define MAX_EXTRA_WAIT_SECONDS 2

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
#define JUDGE_MACHINE_CREATE_MONITOR_THREAD_ERROR "判题机监控线程创建错误"

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
 * @brief 结果JSON未保存
 */
#define RESULT_JSON_NOT_SAVED 0

/**
 * @brief 结果JSON已保存未输出
 */
#define RESULT_JSON_SAVED_NOT_OUTPUTTED 1

/**
 * @brief 结果JSON已保存已输出
 */
#define RESULT_JSON_SAVED_AND_OUTPUTTED 2

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
const char *stdin_path = empty_str;

/**
 * @brief 标准输出
 */
const char *stdout_path = empty_str;

/**
 * @brief 标准错误
 */
const char *stderr_path = empty_str;

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
 * @brief 编译命令
 */
char *compiler_cmd[N] = {NULL};

/**
 * @brief 运行命令
 */
char *run_cmd[N] = {NULL};

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
 * @brief 全局子进程最大运行时间（毫秒）
 */
ull global_time_max_limit = 0;

/**
 * @brief 结果JSON状态
 * 0 未保存
 * 1 保存未完成输出
 * 2 保存已完成输出
 */
int printf_res = RESULT_JSON_NOT_SAVED;

/**
 * @brief 是否是编译模式
 */
bool is_compiler = true;

/**
 * @brief 是否更新过结果状态
 */
bool is_update_res_status = false;

/**
 * @brief 监控子线程是否创建完成
 */
bool monitor_child_thread_creat_finish = false;

/**
 * @brief 沙箱内代码文件路径
 */
const char *sandbox_code_path = empty_str;

/**
 * @brief 代码文件所在文件夹路径
 */
const char *code_directory_path = empty_str;

/**
 * @brief 沙箱内代码文件所在文件夹路径
 */
const char *sandbox_code_directory_path = empty_str;

void init();
void childTimeLimit();
void childMemoryUsed();
void setProcessLimit();
void monitor(pid_t pid);
void unsetMemoryLimit();
void runCode(char *cmd[]);
void cout(bool need_exit);
void run(char *run_cmd[]);
void stdoutToResDataMsg();
void lowerFilePermissions();
void *childTimeoutExit(void *argc);
char *joinStrings(char *strings[]);
char *readFile(const char *filename);
char *jsonEncodeValue(const char *str);
void exitProcess(int exit_code, bool is_child);
void removeSubstring(char *str, const char *sub);
void split(char **arr, char *str, const char *del);
bool judgeSameString(const char *str1, const char *str2);
void closeDup(int &newstdin, int &newstdout, int &newstderr);
char *concatenateStrings(const char *str1, const char *str2);
void creatNamespace(int newstdin, int newstdout, int newstderr);
void joinDeepChroot(int newstdin, int newstdout, int newstderr);
void removePrivileges(int newstdin, int newstdout, int newstderr);
void updateResault(int status, ull time_used, ull memory_used, const char *msg);
void childUpdateResData(int status, ull time_used, ull memory_used, const char *msg);

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
 * @param time_used 时间消耗
 * @param memory_used 内存消耗
 * @param msg 消息
 */
void updateResault(int status, ull time_used, ull memory_used, const char *msg)
{
    if (is_update_res_status)
    {
        exitProcess(EXIT_SUCCESS, false);
        return;
    }
    is_update_res_status = true;
    res_data->status = status;
    res_data->time_used = time_used;
    res_data->memory_used = memory_used;
    res_data->msg = msg;
    exitProcess(EXIT_SUCCESS, false);
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
 * @return buffer 读取的文件内容
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
    ull bytes_read = fread(buffer, 1, size, file);
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
 * @return result 拼接后的字符串
 */
char *concatenateStrings(const char *str1, const char *str2)
{
    ull len1 = strlen(str1);
    ull len2 = strlen(str2);
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
 * @brief 子线程超时退出
 * @param argc 参数
 * @return NULL
 */
void *childTimeoutExit(void *argc)
{
    monitor_child_thread_creat_finish = true;
    errno = 0;
    ull time_cnt = global_time_max_limit;
    do
    {
        --time_cnt;
        usleep(SLEEP_MICROSECONDS);
    } while (time_cnt > 0);
    childMemoryUsed();
    childUpdateResData(LTPP_CODE_TLE, global_time_max_limit, res_data->memory_used, TLE);
    exitProcess(PROCESS_EXIT_SUCCESS, true);
    return NULL;
}

/**
 * @brief 子进程更新结果
 * @param status 状态码
 * @param time_used 时间消耗
 * @param memory_used 内存消耗
 * @param msg 消息
 */
void childUpdateResData(int status, ull time_used, ull memory_used, const char *msg)
{
    res_data->status = status;
    res_data->time_used = time_used;
    res_data->memory_used = memory_used;
    res_data->msg = msg;
}

/**
 * @brief 资源限制
 */
void setProcessLimit()
{
    ull original_limit_time = *time_limit / 1000;
    rl.rlim_cur = original_limit_time + MIN_EXTRA_WAIT_SECONDS;
    rl.rlim_max = original_limit_time + MAX_EXTRA_WAIT_SECONDS;
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
    if (pthread_create(&tid, NULL, childTimeoutExit, NULL) != 0)
    {
        updateResault(LTPP_CODE_SERVER_ERROR, res_data->time_used, res_data->memory_used, JUDGE_MACHINE_CREATE_MONITOR_THREAD_ERROR);
        exitProcess(EXIT_SUCCESS, true);
    }
}

/**
 * @brief 获取文件所在文件夹路径
 * @param filepath 文件路径
 * @return result 文件夹路径
 */
char *extractDirectory(const char *filepath)
{
    char *result = NULL;
    // 查找最后一个斜杠
    const char *lastSlash = strrchr(filepath, '/');
    if (lastSlash != NULL)
    {
        // 加1用于存放字符串结束符 '\0'
        size_t length = lastSlash - filepath + 1;
        result = (char *)malloc(length + 1);
        if (result != NULL)
        {
            strncpy(result, filepath, length);
            // 添加字符串结束符
            result[length] = '\0';
        }
    }
    return result;
}

/**
 * @brief 运行代码
 * @param cmd 运行命令
 */
void runCode(char *cmd[])
{
    errno = 0;
    int cnt = 0;
    int path_loc = 0;
    int newstdin = -1;
    int newstdout = -1;
    int newstderr = -1;
    while (cmd[cnt] != NULL)
    {
        if (strstr(cmd[cnt], SANDBOX_DIRECTORY) != NULL)
        {
            path_loc = cnt;
        }
        ++cnt;
    }
    --cnt;
    // 更新沙箱内代码文件所在文件路径
    // 编译模式为沙箱内代码路径
    // 运行模式为沙箱内可支持文件路径
    sandbox_code_path = strstr(cmd[path_loc], SANDBOX_DIRECTORY) + strlen(SANDBOX_DIRECTORY);
    if (judgeSameString(sandbox_code_directory_path, empty_str))
    {
        // 更新沙箱代码文件所在文件夹路径
        // 去除文件名
        sandbox_code_directory_path = extractDirectory(cmd[path_loc]);
        // 去除沙箱路径
        removeSubstring((char *)sandbox_code_directory_path, SANDBOX_DIRECTORY);
    }
    if (judgeSameString(code_directory_path, empty_str))
    {
        // 更新代码文件所在文件夹路径
        code_directory_path = extractDirectory(cmd[path_loc]);
    }
    if (!is_compiler)
    {
        // 替换为沙箱内的绝对路径
        cmd[path_loc] = (char *)sandbox_code_path;
    }
    const pid_t pid = fork();
    if (pid < 0)
    {
        exitProcess(EXIT_SUCCESS, true);
    }
    else if (pid == 0)
    {
        // 子进程重定向标准输入输出
        newstdin = open(stdin_path, O_RDWR | O_CREAT, 0644);
        newstdout = open(stdout_path, O_RDWR | O_CREAT, 0644);
        newstderr = open(stderr_path, O_RDWR | O_CREAT, 0644);
        setProcessLimit();
        if (newstdout == -1 || newstdin == -1 || newstderr == -1)
        {
            closeDup(newstdin, newstdout, newstderr);
            exitProcess(EXIT_SUCCESS, true);
        }
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
        const int res = execvp(cmd[0], cmd);
        // 兜底，execvp成功会替换子进程，理论上正常情况不会执行下面的代码
        closeDup(newstdin, newstdout, newstderr);
        exitProcess(EXIT_SUCCESS, true);
    }
    else
    {
        // 在运行用户代码进程的父进程进行监控，监控的数据会更加准确
        monitor(pid);
        // 兜底关闭子进程重定向
        closeDup(newstdin, newstdout, newstderr);
        exitProcess(EXIT_SUCCESS, true);
    }
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
        updateResault(LTPP_CODE_SERVER_ERROR, res_data->time_used, res_data->memory_used, NAMESPACE_CREATION_FAILED);
        exitProcess(EXIT_SUCCESS, true);
    }
    // 挂载命名空间（设置只读，对于之前打开的流无影响）
    if (mount(SANDBOX_DIRECTORY, "/", NULL, MS_BIND | MS_RDONLY, NULL) == -1)
    {
        closeDup(newstdin, newstdout, newstderr);
        updateResault(LTPP_CODE_SERVER_ERROR, res_data->time_used, res_data->memory_used, JUDGE_MACHINE_MOUNT_NAMESPACE_ERROR);
        exitProcess(EXIT_SUCCESS, true);
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
        updateResault(LTPP_CODE_SERVER_ERROR, res_data->time_used, res_data->memory_used, JUDGE_MACHINE_SWITCH_USER_ERROR);
        exitProcess(EXIT_SUCCESS, true);
        return;
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
            updateResault(LTPP_CODE_SERVER_ERROR, res_data->time_used, res_data->memory_used, JUDGE_MACHINE_CREATE_NAMESPACE_ERROR);
            exitProcess(EXIT_SUCCESS, true);
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
 * @return encoded_str 序列化后的字符串
 */
char *jsonEncodeValue(const char *str)
{
    if (judgeSameString(str, empty_str))
    {
        // 空串需要单独处理
        return empty_str_arr;
    }
    const ull len = strlen(str);
    // 预留足够大的空间存储编码后的字符串
    // 乘2因为特殊字符串转义后长度为之前2倍
    // 加1因为最后需要一个终止字符
    char *encoded_str = (char *)malloc((ull)(len * 2 + 1) * sizeof(char));
    if (encoded_str == NULL)
    {
        updateResault(LTPP_CODE_SERVER_ERROR, res_data->time_used, res_data->memory_used, JUDGE_MACHINE_SERIALIZATION_EXCEPTION);
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
 * @param int exit_code 状态码
 * @param bool is_child 是否是子进程
 */
void exitProcess(int exit_code, bool is_child)
{
    if (is_child)
    {
        // 获取当前进程的父进程ID
        pid_t current_pid = getpid();
        // 强制杀死所有子进程
        kill(-current_pid, SIGKILL);
        // 兜底退出
        exit(exit_code);
    }
    // false防止循环调用
    cout(false);
    while (true)
    {
        if (printf_res == RESULT_JSON_SAVED_AND_OUTPUTTED)
        {
            exit(exit_code);
        }
        usleep(SLEEP_MICROSECONDS);
    }
}

/**
 * @brief 监控
 * @param pid 进程PID
 */
void monitor(pid_t pid)
{
    errno = 0;
    int status;
    struct rusage ru;
    if (wait4(pid, &status, 0, &ru) == -1)
    {
        updateResault(LTPP_CODE_SERVER_ERROR, res_data->time_used, res_data->memory_used, JUDGE_MACHINE_WAIT_FOR_CHILD_PROCESS_ERROR);
        return;
    }
    const ull time_used = ru.ru_utime.tv_sec * 1000 + ru.ru_utime.tv_usec / 1000 + ru.ru_stime.tv_sec * 1000 + ru.ru_stime.tv_usec / 1000;
    const ull memory_used = ru.ru_maxrss;
    const ull bytes_memory_used = ru.ru_maxrss << 10;
    if (res_data->status != LTPP_CODE_FINISH)
    {
        cout(true);
        return;
    }
    if (WIFSIGNALED(status))
    {
        switch (WTERMSIG(status))
        {
        case SIGSEGV:
        {
            if (time_used > *time_limit)
            {
                updateResault(LTPP_CODE_TLE, time_used, memory_used, TLE);
            }
            else if (bytes_memory_used > *memory_limit)
            {
                updateResault(LTPP_CODE_MLE, time_used, memory_used, MLE);
            }
            else
            {
                updateResault(LTPP_CODE_MLE, time_used, memory_used, RE);
            }
            break;
        }
        case SIGALRM:
        case SIGXCPU:
        {
            updateResault(LTPP_CODE_TLE, time_used, memory_used, TLE);
            break;
        }
        }
    }
    else
    {
        if (time_used > *time_limit)
        {
            updateResault(LTPP_CODE_TLE, time_used, memory_used, TLE);
        }
        else if (memory_used > *memory_limit)
        {
            updateResault(LTPP_CODE_MLE, time_used, memory_used, MLE);
        }
        else if (!isFileEmpty(stderr_path))
        {
            if (is_compiler)
            {
                updateResault(LTPP_CODE_COMPILER_ERROR, time_used, memory_used, USER_CODE_COMPILATION_ERROR);
            }
            else
            {
                updateResault(LTPP_CODE_ERROR, time_used, memory_used, USER_CODE_EXECUTION_ERROR);
            }
        }
    }
    if (!is_compiler)
    {
        // 运行模式才更新正常结束状态
        updateResault(LTPP_CODE_FINISH, time_used, memory_used, empty_str);
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
        // 读取标准输出，防止悬垂指针所以使用static
        static char *out_msg = readFile(stdout_path);
        if (!judgeSameString(out_msg, empty_str))
        {
            if (judgeSameString(res_data->msg, empty_str))
            {
                res_data->msg = out_msg;
            }
            else
            {
                const char *tmp_out_msg = concatenateStrings(br, out_msg);
                res_data->msg = concatenateStrings(res_data->msg, tmp_out_msg);
            }
        }
        return;
    }
    default:
    {
        // 读取错误输出，防止悬垂指针所以使用static
        static char *err_msg = readFile(stderr_path);
        if (!judgeSameString(err_msg, empty_str))
        {
            if (judgeSameString(res_data->msg, empty_str))
            {
                res_data->msg = err_msg;
            }
            else
            {
                const char *tmp_err_msg = concatenateStrings(br, err_msg);
                res_data->msg = concatenateStrings(res_data->msg, tmp_err_msg);
            }
        }
        return;
    }
    }
}

/**
 * @brief 指针数组转字符串
 * @param strings 指针数组
 * @return result 字符串
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
 */
void run(char *cmd[])
{
    errno = 0;
    // 每次运行都需要重置线程创建状态
    monitor_child_thread_creat_finish = false;
    global_time_max_limit = *time_limit + MAX_EXTRA_WAIT_SECONDS * 1000;
    const char *cmd_str = joinStrings(cmd);
    if (cmd_str == NULL || judgeSameString(cmd_str, empty_str))
    {
        // 命令为NULL或者空串则执行以下程序
        if (!is_compiler)
        {
            // 运行模式下需要输出结果
            cout(true);
        }
        // 无运行命令情况下直接返回
        return;
    }
    const pid_t pid = vfork();
    if (pid < 0)
    {
        updateResault(LTPP_CODE_SERVER_ERROR, res_data->time_used, res_data->memory_used, JUDGE_MACHINE_CREATE_CHILD_PROCESS_ERROR);
        return;
    }
    else if (pid == 0)
    {
        // 中间层时间监控，用于兜底
        // 监控时间，防止CPU时间计算异常
        childTimeLimit();
        while (!monitor_child_thread_creat_finish)
        {
            // 等待监控子线程创建完成
            usleep(SLEEP_MICROSECONDS);
        }
        // 运行命令
        runCode(cmd);
    }
    else
    {
        // 等待子进程结束，防止出现孤儿进程和僵尸进程
        const int res = waitpid(pid, NULL, 0);
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
 * @brief 取消内存限制
 */
void unsetMemoryLimit()
{
    struct rlimit rlim;
    // 设置内存限制为无限制
    rlim.rlim_cur = RLIM_INFINITY;
    int res = setrlimit(RLIMIT_DATA, &rlim);
}

/**
 * @brief 输出结果
 * @param bool need_exit 是否退出进程
 */
void cout(bool need_exit)
{
    if (printf_res != RESULT_JSON_NOT_SAVED)
    {
        return;
    }
    printf_res = RESULT_JSON_SAVED_NOT_OUTPUTTED;
    // 取消内存限制
    unsetMemoryLimit();
    // 整合输出结果里的信息
    stdoutToResDataMsg();
    // 去除代码目录路径
    removeSubstring((char *)(res_data->msg), code_directory_path);
    // 去除沙箱内代码目录路径
    removeSubstring((char *)(res_data->msg), sandbox_code_directory_path);
    res_data->msg = jsonEncodeValue(res_data->msg);
    printf("{\"status\":\"%d\",\"time_used\":\"%llu\",\"memory_used\":\"%llu\",\"msg\":\"%s\"}", res_data->status, res_data->time_used, res_data->memory_used, res_data->msg);
    printf_res = RESULT_JSON_SAVED_AND_OUTPUTTED;
    if (need_exit)
    {
        exitProcess(EXIT_SUCCESS, false);
    }
}

/**
 * @brief 删除字符串中的指定子字符串
 * @param str 字符串
 * @param sub 待查找删除的字符串
 */
void removeSubstring(char *str, const char *sub)
{
    int str_len = strlen(str);
    const int sub_len = strlen(sub);
    if (str_len == 0 || sub_len == 0)
    {
        return;
    }
    // 找到目标字符串
    while (strstr(str, sub))
    {
        // 计算目标字符串起始位置
        int find_loc = strstr(str, sub) - str;
        // 结束位置，因为删除字串，所以末尾为字符串长度减去字串长度
        const int str_end_loc = str_len - sub_len;
        for (int i = find_loc; i < str_end_loc; ++i)
        {
            // 字符移动
            str[i] = str[i + sub_len];
        }
        str_len -= sub_len;
        // 结束字符
        str[str_len] = '\0';
    }
}

/**
 * @brief 初始化数据
 */
void init()
{
    is_compiler = false;
    global_time_max_limit = 0;
    is_update_res_status = false;
    printf_res = RESULT_JSON_NOT_SAVED;
    monitor_child_thread_creat_finish = false;
    res_data->msg = empty_str;
    res_data->status = LTPP_CODE_FINISH;
    res_data->time_used = 0;
    res_data->memory_used = 0;
}

/**
 * @brief 更新全局配置
 * @param argv 参数数组
 */
void updateGlobalConfigData(char *argv[])
{
    stdin_path = argv[6];
    stdout_path = argv[7];
    stderr_path = argv[8];
    // static和全局变量生命周期一致，防止悬垂指针
    static ull tmp_compiler_time_limit = atoll(argv[3]);
    static ull tmp_run_time_limit = atoll(argv[4]);
    static ull tmp_run_memory_limit = atoll(argv[5]);
    compiler_time_limit = &tmp_compiler_time_limit;
    run_time_limit = &tmp_run_time_limit;
    run_memory_limit = &tmp_run_memory_limit;
    memory_limit = run_memory_limit;
}

/**
 * @brief 编译
 */
void startCompiler()
{
    // 更新时间限制为编译时间限制
    time_limit = compiler_time_limit;
    // 编译程序
    is_compiler = true;
    run(compiler_cmd);
}

/**
 * @brief 运行
 */
void startRun()
{
    // 更新时间限制为运行时间限制
    time_limit = run_time_limit;
    // 运行程序
    is_compiler = false;
    run(run_cmd);
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
 * @return code 返回code
 */
int main(int argc, char *argv[])
{
    // 初始化结果结构体
    init();
    // 解析编译命令行
    split(compiler_cmd, argv[1], "@");
    // 解析运行命令行
    split(run_cmd, argv[2], "@");
    // 更新全局配置
    updateGlobalConfigData(argv);
    // 编译
    startCompiler();
    // 运行
    startRun();
    return 0;
}