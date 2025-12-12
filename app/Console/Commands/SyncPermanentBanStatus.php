<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncPermanentBanStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:sync-ban-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Đồng bộ trạng thái is_permanently_banned với status của user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Đang đồng bộ trạng thái cấm vĩnh viễn...');
        
        // Cập nhật các user có status = "Cấm" nhưng is_permanently_banned = 0
        $bannedUsers = \App\Models\User::where('status', 'Cấm')
            ->where(function($query) {
                $query->where('is_permanently_banned', false)
                      ->orWhereNull('is_permanently_banned');
            })
            ->get();
        
        $count = 0;
        foreach ($bannedUsers as $user) {
            $user->update([
                'is_permanently_banned' => true,
                'banned_until' => null,
                'ban_reason' => $user->ban_reason ?? 'Bị cấm bởi quản trị viên',
            ]);
            $count++;
        }
        
        $this->info("Đã cập nhật {$count} tài khoản bị cấm.");
        
        // Cập nhật các user có status = "Hoạt động" nhưng is_permanently_banned = 1
        $activeUsers = \App\Models\User::where('status', 'Hoạt động')
            ->where('is_permanently_banned', true)
            ->get();
        
        $count2 = 0;
        foreach ($activeUsers as $user) {
            $user->update([
                'is_permanently_banned' => false,
            ]);
            $count2++;
        }
        
        if ($count2 > 0) {
            $this->info("Đã cập nhật {$count2} tài khoản hoạt động.");
        }
        
        $this->info('Hoàn thành!');
        
        return Command::SUCCESS;
    }
}
