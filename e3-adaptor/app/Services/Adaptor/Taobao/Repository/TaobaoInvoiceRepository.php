<?php


namespace App\Services\Adaptor\Taobao\Repository;

use App\Services\Adaptor\BaseRepository;

/**
 * 天猫发票
 * Class TaobaoInvoiceRepository
 *
 * @package App\Services\Adaptor\Taobao\Repository
 */
class TaobaoInvoiceRepository extends BaseRepository
{
    protected $table = 'taobao_invoice_apply';

    public function updateSyncStatus($tids, $status = 1)
    {
        $this->db()->table($this->table)->whereIn('tid', $tids)->update(['sync_status' => $status]);
    }
}
