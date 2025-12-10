<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
    protected $description = 'Tự động cập nhật trạng thái khuyến mãi dựa trên ngày bắt đầu và kết thúc.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Đang kiểm tra và cập nhật trạng thái khuyến mãi...');
        $now = Carbon::now();

        // 1. Chuyển từ 'scheduled' sang 'active'
        $promotionsToActivate = Promotion::where('status', 'scheduled')
            ->whereDate('start_date', '<=', $now)
            ->get();

        foreach ($promotionsToActivate as $promotion) {
            try {
                $promotion->update(['status' => 'active']);
                $this->info("Đã chuyển mã '{$promotion->code}' từ 'Chờ áp dụng' sang 'Đang chạy'");
            } catch (\Exception $e) {
                $this->error("Lỗi khi cập nhật mã '{$promotion->code}': " . $e->getMessage());
                Log::error("Lỗi cập nhật trạng thái khuyến mãi (scheduled -> active): " . $e->getMessage(), ['promotion_id' => $promotion->id]);
            }
        }

        // 2. Chuyển từ 'active' sang 'expired'
        $promotionsToExpire = Promotion::where('status', 'active')
            ->whereDate('end_date', '<', $now)
            ->get();

        foreach ($promotionsToExpire as $promotion) {
            try {
                $promotion->update(['status' => 'expired']);
                $this->info("Đã chuyển mã '{$promotion->code}' từ 'Đang chạy' sang 'Đã kết thúc'");
            } catch (\Exception $e) {
                $this->error("Lỗi khi cập nhật mã '{$promotion->code}': " . $e->getMessage());
                Log::error("Lỗi cập nhật trạng thái khuyến mãi (active -> expired): " . $e->getMessage(), ['promotion_id' => $promotion->id]);
            }
        }

        // 3. Chuyển từ 'active' sang 'scheduled' nếu start_date chưa đến (trường hợp admin sửa lại ngày)
        $promotionsToSchedule = Promotion::where('status', 'active')
            ->whereDate('start_date', '>', $now)
            ->get();

        foreach ($promotionsToSchedule as $promotion) {
            try {
                $promotion->update(['status' => 'scheduled']);
                $this->info("Đã chuyển mã '{$promotion->code}' sang 'Chờ áp dụng' (ngày bắt đầu: {$promotion->start_date->format('d/m/Y')})");
            } catch (\Exception $e) {
                $this->error("Lỗi khi cập nhật mã '{$promotion->code}': " . $e->getMessage());
                Log::error("Lỗi cập nhật trạng thái khuyến mãi (active -> scheduled): " . $e->getMessage(), ['promotion_id' => $promotion->id]);
            }
        }

        $this->info('Hoàn tất kiểm tra và cập nhật trạng thái khuyến mãi.');
    }
}

