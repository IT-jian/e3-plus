<?php


namespace App\Console\Commands;


use App\Facades\JosClient;
use App\Models\Sys\Shop;
use App\Services\Adaptor\Jingdong\Jobs\SkuDownloadJob;
use App\Services\Platform\Jingdong\Client\Jos\Request\WareReadSearchWare4ValidRequest;
use Illuminate\Console\Command;

class JingdongSkuDownloadCommand extends Command
{
    protected $name = 'adaptor:jingdong:download_sku';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '平台sku同步下载';

    public function handle()
    {
        $where = [];
        if ($this->hasOption('shop_code')) {
            $where['code'] = $this->option('shop_code');
        }
        $shops = Shop::available('jingdong')->where($where)->get();
        foreach ($shops as $shop) {
            $this->download($shop);
        }
    }

    public function download($shop)
    {
        $fields = ['wareId'];

        $request = new WareReadSearchWare4ValidRequest();
        $request->setPageNo(1);
        $request->setPageSize(1);
        $request->setField($fields);

        $result = JosClient::shop($shop['code'])->execute($request);
        // 查询数量
        $total = data_get($result, 'jingdong_sku_read_searchSkuList_responce.page.totalItem', []);
        $pageSize = 50;
        $pageTotal = ceil($total / $pageSize);
        foreach (range(1, $pageTotal) as $pageNo) {
            $params = [
                'page_size' => $pageSize,
                'page_no' => $pageNo,
            ];
            dispatch(new SkuDownloadJob($params));
        }
    }
}
