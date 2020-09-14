<?php


namespace App\Console\Commands;


use App\Services\JingdongCommentCsvExport;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ExportJingdongCommentCommand extends Command
{
    protected $signature = 'adaptor:jingdong:export_comments
                            {--date= : 2020-01-02 导出 1月1日的评论}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '定时导出京东评论并上传';

    public function handle()
    {
        if ($this->hasOption('date') && !empty($this->option('date'))) {
            $date = $this->option('date');
        } else {
            $date = Carbon::now()->subDay()->toDateString();
        }

        $server = new JingdongCommentCsvExport();
        // 导出
        $server->exportByDate($date);
        // 上传
        $server->upload($date);
    }
}
