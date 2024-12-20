<?php

namespace VodaPay\VodaPay\Cron;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Helper\Data;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Payment\Transaction\Builder;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Store\Model\StoreManagerInterface;
use VodaPay\VodaPay\Gateway\Config\Config;
use VodaPay\VodaPay\Gateway\Http\Client\TransactionFetch;
use VodaPay\VodaPay\Gateway\Http\TransferFactory;
use VodaPay\VodaPay\Gateway\Request\TokenRequest;
use VodaPay\VodaPay\Model\CoreFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use VodaPay\VodaPay\Controller\VodaPay\Payment;

/**
 * Cron Driver
 *
 * Class UpdateOrder
 */
class UpdateOrder
{
    /**
     * @var Payment
     */
    private Payment $vodapayPaymentTools;
    /**
     * @var State
     */
    private State $state;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param ManagerInterface $messageManager
     * @param PageFactory $pageFactory
     * @param RequestInterface $request
     * @param Data $checkoutHelper
     * @param Config $config
     * @param TokenRequest $tokenRequest
     * @param StoreManagerInterface $storeManager
     * @param TransferFactory $transferFactory
     * @param TransactionFetch $transaction
     * @param CoreFactory $coreFactory
     * @param BuilderInterface $transactionBuilder
     * @param ResultFactory $resultRedirect
     * @param InvoiceService $invoiceService
     * @param TransactionFactory $transactionFactory
     * @param InvoiceSender $invoiceSender
     * @param OrderSender $orderSender
     * @param OrderFactory $orderFactory
     * @param LoggerInterface $logger
     * @param Session $checkoutSession
     * @param Product $productCollection
     * @param SerializerInterface $serializer
     * @param Builder $_transactionBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param State $state
     */
    public function __construct(
        ManagerInterface $messageManager,
        PageFactory $pageFactory,
        RequestInterface $request,
        Data $checkoutHelper,
        Config $config,
        TokenRequest $tokenRequest,
        StoreManagerInterface $storeManager,
        TransferFactory $transferFactory,
        TransactionFetch $transaction,
        CoreFactory $coreFactory,
        BuilderInterface $transactionBuilder,
        ResultFactory $resultRedirect,
        InvoiceService $invoiceService,
        TransactionFactory $transactionFactory,
        InvoiceSender $invoiceSender,
        OrderSender $orderSender,
        OrderFactory $orderFactory,
        LoggerInterface $logger,
        Session $checkoutSession,
        Product $productCollection,
        SerializerInterface $serializer,
        Builder $_transactionBuilder,
        OrderRepositoryInterface $orderRepository,
        State $state
    ) {
        $this->vodapayPaymentTools = new Payment(
            $messageManager,
            $pageFactory,
            $request,
            $checkoutHelper,
            $config,
            $tokenRequest,
            $storeManager,
            $transferFactory,
            $transaction,
            $coreFactory,
            $transactionBuilder,
            $resultRedirect,
            $invoiceService,
            $transactionFactory,
            $invoiceSender,
            $orderSender,
            $orderFactory,
            $logger,
            $checkoutSession,
            $productCollection,
            $serializer,
            $_transactionBuilder,
            $orderRepository
        );
        $this->state               = $state;
        $this->logger              = $logger;
    }

    /**
     * Default execute function.
     *
     * @return null
     * @throws Exception
     */
    public function execute()
    {
        $this->state->emulateAreaCode(
            Area::AREA_FRONTEND,
            function () {
                try {
                    $this->logger->info('N-GENIUS cron started');
                    $this->vodapayPaymentTools->cronTask();
                    $this->logger->info('N-GENIUS cron ended');
                } catch (Exception $ex) {
                    $this->logger->error($ex->getMessage());
                }
            }
        );
    }
}
