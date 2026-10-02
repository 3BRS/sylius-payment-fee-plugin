<?php

declare(strict_types=1);

use Behat\Config\Config;

return (new Config())
    ->import([
        'tests/Behat/Resources/config/suites/ui_checkout.php',
        'tests/Behat/Resources/config/suites/payment_fee_verification.php',
    ]);
