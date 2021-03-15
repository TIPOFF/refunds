<?php

declare(strict_types=1);

namespace Tipoff\Refunds\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Tipoff\Refunds\Enums\RefundMethod;
use Tipoff\Refunds\Nova\Actions\IssueRefund;
use Tipoff\Support\Nova\BaseResource;
use Tipoff\Support\Nova\Fields\Enum;

class Refund extends BaseResource
{
    public static $model = \Tipoff\Refunds\Models\Refund::class;

    public static $title = 'refund_number';

    public static $search = [
        'id',
        'refund_number',
    ];

    protected array $actionClassList = [
        IssueRefund::class,
    ];

    public function fieldsForIndex(NovaRequest $request)
    {
        return array_filter([
            ID::make()->sortable(),
            nova('payment') ? BelongsTo::make('Payment', 'payment', nova('payment'))->sortable() : null,
            Text::make('Refund Number')->sortable(),
            Currency::make('Amount')->asMinorUnits()->sortable(),
            nova('voucher') ? BelongsTo::make('Voucher', 'voucher', nova('voucher'))->sortable() : null,
            Date::make('Requested At', 'created_at')->sortable(),
            Date::make('Issued At', 'issued_at')->sortable(),
        ]);
    }

    public function fields(Request $request)
    {
        return array_filter([
            // Refunds cannot be created here. They must be issued through an action on the Payment resource.
            nova('payment') ? BelongsTo::make('Payment', 'payment', nova('payment'))->exceptOnForms() : null,
            Text::make('Refund Number')->exceptOnForms(),
            Currency::make('Amount')->asMinorUnits()->exceptOnForms(),
            nova('voucher') ? BelongsTo::make('Voucher', 'voucher', nova('voucher'))->exceptOnForms() : null,
            Enum::make('Method')->attach(RefundMethod::class)->exceptOnForms(),
            Date::make('Requested At', 'created_at')->exceptOnForms(),
            Date::make('Issued At', 'issued_at')->exceptOnForms(),
            nova('user') ? BelongsTo::make('Issued By', 'issuer', nova('user'))->exceptOnForms() : null,
            Text::make('Transaction Number')->exceptOnForms(),

            new Panel('Data Fields', $this->dataFields()),
        ]);
    }

    protected function dataFields(): array
    {
        return array_filter([
            ID::make(),
            nova('user') ? BelongsTo::make('Created By', 'creator', nova('user'))->exceptOnForms() : null,
            DateTime::make('Created At')->exceptOnForms(),
            nova('user') ? BelongsTo::make('Updated By', 'updater', nova('user'))->exceptOnForms() : null,
            DateTime::make('Updated At')->exceptOnForms(),
        ]);
    }
}
