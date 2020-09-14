<?php


namespace App\Console\Commands;


use App\Models\Sys\Shop;
use App\Services\PlatformSkuInventoryCsvExport;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ExportPlatformSkuInventoryCommand extends Command
{
    protected $name = 'adaptor:export_platform_sku_inventory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '定时导出平台商品信息并上传';

    public function handle()
    {
        if ($this->hasOption('date')) {
            $date = $this->option('date');
        } else {
            $date = Carbon::now()->toDateTimeString();
        }
        $shops = Shop::all();
        foreach ($shops as $shop) { // 按照店铺导出
            $server = new PlatformSkuInventoryCsvExport();
            // 导出
            $server->exportByDate($date, $shop['code']);
            // 上传
            $server->upload($date);
        }
    }
}
