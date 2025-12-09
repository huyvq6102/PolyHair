<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdatePromotionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'promotions:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tự động cập nhật trạng thái khuyến mãi dựa trên ngày bắt đầu và kết thúc';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Đang kiểm tra và cập nhật trạng thái khuyến mãi...');
        
        $today = Carbon::today();
        $updatedCount = 0;
        
        // Cập nhật các mã "Chờ áp dụng" thành "Đang chạy" khi đến ngày bắt đầu
        $scheduledPromotions = Promotion::where('status', 'scheduled')
            ->whereDate('start_date', '<=', $today)
            ->get();
        
        foreach ($scheduledPromotions as $promotion) {
            try {
                DB::transaction(function () use ($promotion, &$updatedCount) {
                    $promotion->update(['status' => 'active']);
                    $updatedCount++;
                    $this->info("Đã chuyển mã '{$promotion->code}' từ 'Chờ áp dụng' sang 'Đang chạy'");
                });
            } catch (\Exception $e) {
                $this->error("Lỗi khi cập nhật mã '{$promotion->code}': " . $e->getMessage());
            }
        }
        
        // Cập nhật các mã "Đang chạy" thành "Đã kết thúc" khi hết ngày kết thúc
        $activePromotions = Promotion::where('status', 'active')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', $today)
            ->get();
        
        foreach ($activePromotions as $promotion) {
            try {
                DB::transaction(function () use ($promotion, &$updatedCount) {
                    $promotion->update(['status' => 'expired']);
                    $updatedCount++;
                    $this->info("Đã chuyển mã '{$promotion->code}' từ 'Đang chạy' sang 'Đã kết thúc'");
                });
            } catch (\Exception $e) {
                $this->error("Lỗi khi cập nhật mã '{$promotion->code}': " . $e->getMessage());
            }
        }
        
        // Tự động đặt trạng thái "Chờ áp dụng" cho các mã có ngày bắt đầu trong tương lai
        $futurePromotions = Promotion::whereIn('status', ['active', 'inactive'])
            ->whereDate('start_date', '>', $today)
            ->get();
        
        foreach ($futurePromotions as $promotion) {
            try {
                DB::transaction(function () use ($promotion, &$updatedCount) {
                    $promotion->update(['status' => 'scheduled']);
                    $updatedCount++;
                    $this->info("Đã chuyển mã '{$promotion->code}' sang 'Chờ áp dụng' (ngày bắt đầu: {$promotion->start_date->format('d/m/Y')})");
                });
            } catch (\Exception $e) {
                $this->error("Lỗi khi cập nhật mã '{$promotion->code}': " . $e->getMessage());
            }
        }
        
        if ($updatedCount > 0) {
            $this->info("Đã cập nhật {$updatedCount} mã khuyến mãi.");
        } else {
            $this->info("Không có mã khuyến mãi nào cần cập nhật.");
        }
        
        return Command::SUCCESS;
    }
}

