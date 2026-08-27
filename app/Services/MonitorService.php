<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;

class MonitorService
{
    // ─────────────────────────────────────────────
    //  SERVER STATS
    // ─────────────────────────────────────────────

    public function getServerStats(): array
    {
        $cpuRaw = Process::run("top -bn1 | grep 'Cpu(s)' | sed 's/.*, *\\([0-9.]*\\)%* id.*/\\1/' | awk '{print 100 - $1}'")->output();
        $cpuIdle = Process::run("vmstat 1 1 | tail -1 | awk '{print $15}'")->output();

        $ramTotal = (int) trim(Process::run("free -m | awk '/Mem:/ {print $2}'")->output());
        $ramUsed = (int) trim(Process::run("free -m | awk '/Mem:/ {print $3}'")->output());
        $ramFree = (int) trim(Process::run("free -m | awk '/Mem:/ {print $4}'")->output());
        $ramCache = (int) trim(Process::run("free -m | awk '/Mem:/ {print $6}'")->output());

        $swapTotal = (int) trim(Process::run("free -m | awk '/Swap:/ {print $2}'")->output());
        $swapUsed = (int) trim(Process::run("free -m | awk '/Swap:/ {print $3}'")->output());

        $diskRoot = trim(Process::run("df -h / | awk 'NR==2 {print $3\"/\"$2\" (\"$5\")\"  }'")->output());
        $diskPercent = (int) trim(Process::run("df / | awk 'NR==2 {gsub(\"%\",\"\",$5); print $5}'")->output());

        $loadAvg = trim(Process::run("cat /proc/loadavg | awk '{print $1, $2, $3}'")->output());
        $cpuCores = (int) trim(Process::run("nproc")->output());

        $uptime = trim(Process::run("uptime -p")->output());
        $uptimeS = trim(Process::run("cat /proc/uptime | awk '{print $1}'")->output());

        $hostname = trim(Process::run("hostname")->output());
        $os = trim(Process::run("uname -srm")->output());
        $phpVer = PHP_VERSION;
        $laraVer = app()->version();

        // Network I/O (bytes) via /proc/net/dev for the primary interface
        $netIface = trim(Process::run("ip route get 8.8.8.8 2>/dev/null | awk '{print $5; exit}'")->output()) ?: 'eth0';
        $netRx = trim(Process::run("cat /proc/net/dev | grep '{$netIface}' | awk '{print $2}'")->output());
        $netTx = trim(Process::run("cat /proc/net/dev | grep '{$netIface}' | awk '{print $10}'")->output());

        // Prettier approach using /sys
        $sysNetRx = @file_get_contents("/sys/class/net/{$netIface}/statistics/rx_bytes") ?: '0';
        $sysNetTx = @file_get_contents("/sys/class/net/{$netIface}/statistics/tx_bytes") ?: '0';

        // Open file descriptors
        $openFiles = trim(Process::run("cat /proc/sys/fs/file-nr | awk '{print $1}'")->output());
        $maxFiles = trim(Process::run("cat /proc/sys/fs/file-nr | awk '{print $3}'")->output());

        // TCP connections
        $tcpConns = trim(Process::run("ss -s | grep 'TCP:' | awk '{print $2}'")->output());
        $estabConn = trim(Process::run("ss -tn state established | tail -n +2 | wc -l")->output());

        $cpuPercent = round((float) ($cpuRaw ?: (100 - (float) $cpuIdle)), 2);

        return [
            'cpu' => $cpuPercent,
            'cpu_cores' => $cpuCores,
            'load_avg' => $loadAvg,
            'ram_used' => $ramUsed,
            'ram_free' => $ramFree,
            'ram_total' => $ramTotal,
            'ram_cache' => $ramCache,
            'ram_percent' => $ramTotal > 0 ? round(($ramUsed / $ramTotal) * 100, 2) : 0,
            'swap_used' => $swapUsed,
            'swap_total' => $swapTotal,
            'swap_percent' => $swapTotal > 0 ? round(($swapUsed / $swapTotal) * 100, 2) : 0,
            'disk' => $diskRoot,
            'disk_percent' => $diskPercent,
            'uptime' => $uptime,
            'uptime_s' => (float) $uptimeS,
            'hostname' => $hostname,
            'os' => $os,
            'php_version' => $phpVer,
            'laravel_version' => $laraVer,
            'net_iface' => $netIface,
            'net_rx_bytes' => (int) trim($sysNetRx),
            'net_tx_bytes' => (int) trim($sysNetTx),
            'open_files' => (int) $openFiles,
            'max_files' => (int) $maxFiles,
            'tcp_conns' => (int) $tcpConns,
            'estab_conns' => (int) $estabConn,
        ];
    }

    // ─────────────────────────────────────────────
    //  TOP PROCESSES (CPU & RAM)
    // ─────────────────────────────────────────────

    public function getTopProcesses(int $limit = 15): array
    {
        // ps: pid, %cpu, %mem, vsz(kb), rss(kb), stat, user, command
        $raw = Process::run(
            "ps aux --sort=-%cpu | awk 'NR>1 {printf \"%s|%s|%s|%s|%s|%s|%s|\", $1,$2,$3,$4,$5,$6,$8; for(i=11;i<=NF;i++) printf \"%s \", $i; print \"\"}' | head -n {$limit}"
        )->output();

        $processes = [];
        foreach (explode("\n", trim($raw)) as $line) {
            if (empty(trim($line)))
                continue;
            $p = explode('|', $line);
            if (count($p) < 8)
                continue;
            $processes[] = [
                'user' => trim($p[0]),
                'pid' => (int) trim($p[1]),
                'cpu' => (float) trim($p[2]),
                'mem' => (float) trim($p[3]),
                'vsz_mb' => round((int) trim($p[4]) / 1024, 1),
                'rss_mb' => round((int) trim($p[5]) / 1024, 1),
                'stat' => trim($p[6]),
                'command' => substr(trim($p[7]), 0, 60),
            ];
        }
        return $processes;
    }

    // ─────────────────────────────────────────────
    //  KILL A PROCESS
    // ─────────────────────────────────────────────

    public function killProcess(int $pid): array
    {
        if ($pid <= 1) {
            return ['success' => false, 'message' => 'Cannot kill system process.'];
        }

        // Disallow killing critical system processes
        $cmd = trim(Process::run("ps -p {$pid} -o comm= 2>/dev/null")->output());
        $protected = ['init', 'systemd', 'sshd', 'nginx', 'apache2', 'mysql', 'php-fpm'];
        if (in_array(strtolower($cmd), $protected)) {
            return ['success' => false, 'message' => "Cannot kill protected process: {$cmd}"];
        }

        $result = Process::run("kill -15 {$pid} 2>&1");
        if ($result->exitCode() !== 0) {
            // Try SIGKILL
            $result = Process::run("kill -9 {$pid} 2>&1");
        }

        return [
            'success' => $result->exitCode() === 0,
            'message' => $result->exitCode() === 0
                ? "Process {$pid} ({$cmd}) terminated successfully."
                : "Failed to kill process {$pid}: " . trim($result->output()),
        ];
    }

    // ─────────────────────────────────────────────
    //  DATABASE TABLE STATS
    // ─────────────────────────────────────────────

    public function getTableStats(): array
    {
        $dbName = config('database.connections.mysql.database');
        return DB::select("
            SELECT
                table_name         AS name,
                table_rows         AS count,
                ROUND(((data_length + index_length) / 1024 / 1024), 4) AS size_mb,
                ROUND((data_length / 1024 / 1024), 4)                  AS data_mb,
                ROUND((index_length / 1024 / 1024), 4)                 AS index_mb,
                ROUND((data_free / 1024 / 1024), 4)                    AS free_mb,
                table_collation    AS collation,
                engine             AS engine,
                create_time        AS created_at,
                update_time        AS updated_at,
                auto_increment     AS auto_increment
            FROM information_schema.TABLES
            WHERE table_schema = ?
            ORDER BY size_mb DESC
        ", [$dbName]);
    }

    // ─────────────────────────────────────────────
    //  MYSQL SERVER STATUS
    // ─────────────────────────────────────────────

    public function getMysqlStatus(): array
    {
        $vars = DB::select("SHOW GLOBAL VARIABLES WHERE Variable_name IN
            ('max_connections','innodb_buffer_pool_size','query_cache_size',
             'version','hostname','datadir')");
        $status = DB::select("SHOW GLOBAL STATUS WHERE Variable_name IN
            ('Threads_connected','Threads_running','Questions','Slow_queries',
             'Uptime','Bytes_received','Bytes_sent','Com_select','Com_insert',
             'Com_update','Com_delete','Aborted_connects','Table_locks_waited',
             'Innodb_buffer_pool_reads','Innodb_buffer_pool_read_requests')");

        $out = [];
        foreach (array_merge($vars, $status) as $row) {
            $out[strtolower($row->Variable_name)] = $row->Value;
        }

        // Calculate buffer pool hit rate
        $reads = (int) ($out['innodb_buffer_pool_reads'] ?? 0);
        $requests = (int) ($out['innodb_buffer_pool_read_requests'] ?? 1);
        $out['buffer_pool_hit_rate'] = $requests > 0
            ? round((1 - $reads / $requests) * 100, 2)
            : 100;

        // Human-readable sizes
        if (isset($out['innodb_buffer_pool_size'])) {
            $out['innodb_buffer_pool_size_mb'] = round($out['innodb_buffer_pool_size'] / 1024 / 1024, 2);
        }
        if (isset($out['bytes_received'])) {
            $out['bytes_received_mb'] = round($out['bytes_received'] / 1024 / 1024, 2);
        }
        if (isset($out['bytes_sent'])) {
            $out['bytes_sent_mb'] = round($out['bytes_sent'] / 1024 / 1024, 2);
        }

        return $out;
    }

    // ─────────────────────────────────────────────
    //  MYSQL PROCESS LIST (active queries)
    // ─────────────────────────────────────────────

    public function getMysqlProcessList(): array
    {
        return DB::select("SELECT Id, User, Host, db AS `database`, Command, Time, State, LEFT(Info, 120) AS Query FROM information_schema.PROCESSLIST WHERE Command != 'Sleep' ORDER BY Time DESC LIMIT 20");
    }

    // ─────────────────────────────────────────────
    //  SLOW QUERIES LOG (last N from slow log table if enabled)
    // ─────────────────────────────────────────────

    public function getSlowQueries(int $limit = 10): array
    {
        try {
            return DB::select("SELECT start_time, query_time, lock_time, rows_examined, rows_sent, LEFT(sql_text,200) AS sql_text FROM mysql.slow_log ORDER BY start_time DESC LIMIT ?", [$limit]);
        } catch (\Throwable) {
            return [];
        }
    }

    // ─────────────────────────────────────────────
    //  LARAVEL QUEUE / JOBS SUMMARY
    // ─────────────────────────────────────────────

    public function getJobStats(): array
    {
        try {
            $pending = DB::table('jobs')->count();
            $failed = DB::table('failed_jobs')->count();
            $batches = DB::table('job_batches')->count();
        } catch (\Throwable) {
            return ['pending' => 0, 'failed' => 0, 'batches' => 0, 'available' => false];
        }
        return ['pending' => $pending, 'failed' => $failed, 'batches' => $batches, 'available' => true];
    }

    // ─────────────────────────────────────────────
    //  DISK I/O STATS
    // ─────────────────────────────────────────────

    public function getDiskIO(): array
    {
        $raw = trim(Process::run("iostat -dx 1 1 2>/dev/null | awk 'NR>3 && NF>0 {print $1\"|\"$4\"|\"$5\"|\"$14}' | head -5")->output());
        $devices = [];
        foreach (explode("\n", $raw) as $line) {
            $p = explode('|', $line);
            if (count($p) < 4)
                continue;
            $devices[] = [
                'device' => trim($p[0]),
                'read_mb_s' => round((float) trim($p[1]) / 1024, 3),
                'write_mb_s' => round((float) trim($p[2]) / 1024, 3),
                'util_pct' => (float) trim($p[3]),
            ];
        }
        return $devices;
    }

    // ─────────────────────────────────────────────
    //  PHP-FPM STATUS (if running)
    // ─────────────────────────────────────────────

    public function getPhpFpmStatus(): array
    {
        $raw = trim(Process::run("php-fpm -t 2>&1 | head -1")->output());
        $pool = trim(Process::run("curl -sf 127.0.0.1/fpm-status?json 2>/dev/null")->output());
        if ($pool) {
            try {
                return ['available' => true, 'data' => json_decode($pool, true)];
            } catch (\Throwable) {
            }
        }
        return ['available' => false, 'data' => []];
    }
}