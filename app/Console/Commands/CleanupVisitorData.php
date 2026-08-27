<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Visitor;
use App\Models\VisitorSession;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CleanupVisitorData extends Command
{
    protected $signature = 'visitors:cleanup {--days=90 : Number of days to keep}';
    protected $description = 'Clean up old visitor data';

    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Cleaning up visitor data older than {$days} days...");

        // Delete old sessions first due to foreign key constraints
        $sessionCount = VisitorSession::where('started_at', '<', $cutoffDate)->delete();
        $this->info("Deleted {$sessionCount} old sessions.");

        // Delete visitors who haven't been seen since cutoff and have no sessions
        $visitorCount = Visitor::where('last_seen_at', '<', $cutoffDate)
            ->doesntHave('sessions')
            ->delete();
            
        $this->info("Deleted {$visitorCount} old visitors.");

        // Delete orphaned page views and events
        $pageViewCount = DB::table('page_views')
            ->whereNotIn('visitor_id', function($query) {
                $query->select('id')->from('visitors');
            })
            ->delete();
            
        $this->info("Deleted {$pageViewCount} orphaned page views.");

        $eventCount = DB::table('visitor_events')
            ->whereNotIn('visitor_id', function($query) {
                $query->select('id')->from('visitors');
            })
            ->delete();
            
        $this->info("Deleted {$eventCount} orphaned events.");

        $this->info('Visitor data cleanup completed.');
    }
}