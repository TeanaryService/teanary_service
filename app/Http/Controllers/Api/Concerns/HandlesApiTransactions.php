<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Support\Facades\DB;

trait HandlesApiTransactions
{
    /**
     * 检查是否已经在事务中
     *
     * @return bool
     */
    protected function isInTransaction(): bool
    {
        return DB::transactionLevel() > 0;
    }

    /**
     * 开始事务（如果不在事务中）
     *
     * @return bool 是否开启了新事务
     */
    protected function beginTransactionIfNotInOne(): bool
    {
        if (!$this->isInTransaction()) {
            DB::beginTransaction();
            return true;
        }

        return false;
    }

    /**
     * 提交事务（如果开启了新事务）
     *
     * @param bool $openedTransaction 是否开启了新事务
     * @return void
     */
    protected function commitIfOpened(bool $openedTransaction): void
    {
        if ($openedTransaction) {
            DB::commit();
        }
    }

    /**
     * 回滚事务（如果开启了新事务）
     *
     * @param bool $openedTransaction 是否开启了新事务
     * @return void
     */
    protected function rollbackIfOpened(bool $openedTransaction): void
    {
        if ($openedTransaction) {
            DB::rollBack();
        }
    }
}

