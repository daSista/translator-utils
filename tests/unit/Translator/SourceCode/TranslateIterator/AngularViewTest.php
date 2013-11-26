<?php

namespace Translator\SourceCode\TranslateIterator;

class AngularViewTest extends \PHPUnit_Framework_TestCase
{
    public function testCanIterateOverEachTranslationKeys()
    {
        $translations = array();
        $iterator = new AngularView;

        foreach ($iterator->select(__DIR__ . '/data/angular-view.html') as $key => $paramNames) {
            $translations[$key] = $paramNames;
        }

        $this->assertEquals(
            array(
                'orderNumber' => null,
                'orderComment' => null,
                'notPaid' => null,
                'paid' => null,
                'affiliate' => null,
                'customerName' => null,
                'paidOn' => null,
                'ticket:tariff' => null,
                'ticket:firstname' => null,
                'ticket:lastname' => null,
                'ticket:startDate' => null,
                'ticket:class' => null,
                'ticket:validity' => null,
                'ticket:discount' => null,
                'ticket:amount' => null,
                'ticket:price' => null,
                'order/billing:addressTitle' => null,
                'order/delivery:addressTitle' => null,
                'order/delivery:information' => null,
                'order/delivery:type' => null,
                'order/delivery:flightArrivalDate' => null,
                'order/delivery:flightNumber' => null,
                'order/delivery:flightArrivalTime' => null,
                'order/delivery:flightFrom' => null,
                'order:notes' => null,
                'order:totalsTitle' => null,
                'order:deliveryFee' => null,
                'order:paymentFee' => null,
                'order:bookingFee' => null,
                'order:subtotal' => null,
                'order:total' => null,
                'downloadBBI' => null,
                'backToList' => null,
            ),
            $translations
        );
    }
}
