<?php

declare(strict_types=1);

namespace Tipoff\Refunds\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class IssueRefund extends Action
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        /** @psalm-suppress */
        $models->each(function ($model) use ($fields) {
            $model->issue();
            $model->notifyCustomer();
        });

        return Action::message($fields->type.' refund issued.');
    }
}
