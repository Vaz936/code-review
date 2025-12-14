<?php

declare(strict_types=1);

namespace Modules\Payment\Domain\Components\Actions\YandexpayLink;

use common\modules\exceptions\ForbiddenException;
use common\modules\exceptions\NotFoundException;
use Exception;
use Modules\Payment\Application\Managers\Dto\DeactivatePaymentLinkDto;
use Modules\Payment\Domain\Components\Actions\PaymentActionInterface;
use Modules\Payment\Domain\Components\PaymentSettings\YandexpaySettingSet;
use Modules\Payment\Domain\Models\Mysql\Tables\PaymentStatus;
use Modules\Payment\Domain\Models\Mysql\Tables\PaymentTransaction;
use Modules\Payment\Infrastructure\Libraries\Yandex\Library;

/**
 * Платежное действие - деактивация платежной ссылки для оплаты через Яндекс Пей
 */
readonly class DeactivatePaymentLinkAction implements PaymentActionInterface
{
    /**
     * Получение данных и модели из компонента {@see AbstractActionComponent::init()}
     * @param DeactivatePaymentLinkDto $deactivateDto Набор данных для деактивации платежной ссылки через Яндекс Пей
     */
    public function __construct(
        private DeactivatePaymentLinkDto $deactivateDto,
        private PaymentStatus $paymentStatus
    ) {}

    /**
     * Выполнение запроса
     */
    public function execute()
    {
        try {
            $this->paymentStatus->refresh();
            if ($this->paymentStatus->is_paid == 1) {
                throw new ForbiddenException('Entity is already paid');
            }
            $paymentTransaction = PaymentTransaction::find()
                ->byId($this->deactivateDto->paymentTransactionId)
                ->byType(PaymentTransactionTypeDictionary::Income->value)
                ->first();
            if ($paymentTransaction === null) {
                throw new NotFoundException('No transaction to deactivate');
            }
            if (empty($paymentTransaction->merchant_id)) {
                throw new NotFoundException('No merchant ID in transaction ' . $paymentTransaction->id);
            }
            $settings = YandexpaySettingSet::make()->setByMerchantId($paymentTransaction->merchant_id);
            $library  = Library::make();
            $library->setLibraryConfig($settings->getSecretKey());
            $library->deactivatePaymentLink($paymentTransaction->transaction_id);
        } catch (Exception $e) {
        }
    }
}
