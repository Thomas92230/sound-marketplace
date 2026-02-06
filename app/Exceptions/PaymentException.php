<?php

namespace App\Exceptions;

use Exception;

class PaymentException extends Exception
{
    public static function alreadyPurchased(): self
    {
        return new self('Vous avez déjà acheté ce morceau.');
    }

    public static function cannotPurchaseOwnTrack(): self
    {
        return new self('Vous ne pouvez pas acheter votre propre morceau.');
    }

    public static function stripeError(string $message): self
    {
        return new self('Erreur de paiement Stripe: ' . $message);
    }
}
