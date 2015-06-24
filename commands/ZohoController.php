<?php

namespace app\commands;

use app\models\Contact;
use app\models\ContactQueue;
use app\models\Invoice;
use app\models\InvoiceQueue;
use Yii;
use yii\base\Exception;
use yii\console\Controller;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ZohoController extends Controller
{
    public function actionIndex()
    {
        throw new Exception('Bla bla error');

        InvoiceQueue::invoicesList();
        Invoice::updateInvoices();

        ContactQueue::contactsList();
        Contact::updateContacts();
    }
}
