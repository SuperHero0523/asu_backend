<?php

/*
 * This file is part of Laravel Ban.
 *
 * (c) Anton Komarev <a.komarev@cybercog.su>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Observers;

use Cog\Contracts\Ban\Ban as BanContract;
use Cog\Laravel\Ban\Events\ModelWasBanned;
use Cog\Laravel\Ban\Events\ModelWasUnbanned;

use App\User;


/**
 * Class BanObserver.
 *
 * @package Cog\Laravel\Ban\Observers
 */
class BanObserver
{
    /**
     * Handle the creating event for the Ban model.
     *
     * @param \Cog\Contracts\Ban\Ban $ban
     * @return void
     */
    public function creating(BanContract $ban)
    {
        $bannedBy = $ban->created_by_id ? 
                        User::find($ban->created_by_id) 
                    : 
                        auth()->user();

        if ($bannedBy) {
            $ban->forceFill([
                'created_by_id' => $bannedBy->getKey(),
                'created_by_type' => $bannedBy->getMorphClass(),
            ]);
        }
    }
    
    /**
     * Handle the created event for the Ban model.
     *
     * @param \Cog\Contracts\Ban\Ban $ban
     * @return void
     */
    public function created(BanContract $ban)
    {
        $bannable = $ban->bannable()->withBanned()->first();
        $bannable->setBannedFlag($ban->created_at)->save();

        event(new ModelWasBanned($bannable, $ban));
    }

    /**
     * Handle the deleted event for the Ban model.
     *
     * @param \Cog\Contracts\Ban\Ban $ban
     * @return void
     */
    public function deleted(BanContract $ban)
    {
        $bannable = $ban->bannable()->withBanned()->first();
        if ($bannable->bans->count() === 0) {
            $bannable->unsetBannedFlag()->save();

            event(new ModelWasUnbanned($bannable));
        }
    }

}
