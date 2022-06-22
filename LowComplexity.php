<?php

class Reduction 
{
    public function checkDeclinationsForValue($declinations) {
        $validDeclinations = array();
        $nbNights = 0;
       
        foreach ($declinations as $declination) {
            $isAttributIsEligible = $declination->getDeclination()->getProduitAttributeValeurFromIdAttribut(\Attribut::KEY_STAY_PERIOD) ||
                $declination->getDeclination()->getProduitAttributeValeurFromIdAttribut(\Attribut::KEY_VISIT);
            
            $nbNights += $declination->getDeclination()->getNbNightDeclination();
            $validDeclinations[] = $declination;

            if ($isAttributIsEligible && $nbNights >= ($this->getBoughtNights() + $this->getFreeNights())) {
                $this->setValidDeclinationsForSale($validDeclinations);
                return true;
            }
        }

        return false;
    }

    public function calculateValue()
    {
        if ($this->getUnit() === self::UNIT_EURO) return $this->calculateValueForEuro() ;
        if ($this->getUnit() === self::UNIT_PERCENT) return $this->calculateValueForPercentage();
    }

    private function calculateValueForEuro() {
        $value = 0;
        $totalPrice = 0;

        foreach ($this->getValidDeclinationsForSale() as $declination) {
            $declinationSale = 0;
            $currentPrice = $this->getCurrentPriceForDeclination($declination);

            /* @var $declination \LogiCE\Entity\Order\Declination */
            $totalPrice += $currentPrice;

            if (!$decrementedValue <= 0) {
                $decrementedValue -= $currentPrice <= $decrementedValue ? $currentPrice : $currentPrice - $decrementedValue;
            }

            $this->updateCurrentPriceForDeclination($declination, $declinationSale);
            $this->addSalesToDeclination($declination, $declinationSale);
        }

        return $this->getPercentage() - $decrementedValue;
    }

    private function calculateValueForPercentage() {
        $value = 0;
        $totalPrice = 0;
        $decrementedValue = $this->getPercentage();

        foreach ($this->getValidDeclinationsForSale() as $declination) {
            $declinationSale = 0;
            $currentPrice = $this->getCurrentPriceForDeclination($declination);

            /* @var $declination \LogiCE\Entity\Order\Declination */
            $totalPrice += $currentPrice;
            $declinationSale = ($this->getPercentage() / 100) * $currentPrice;
            $this->updateCurrentPriceForDeclination($declination, $declinationSale);
            $this->addSalesToDeclination($declination, $declinationSale);
        }
        
        return ($this->getPercentage() / 100) * $totalPrice;
    }

}


class DateHelper
{
    public static function addMonthRefactor($date, $nbMonths): ?string
    {
        $arrayDate = explode('-', $date);

        if (count($arrayDate) <= 1) {
            return null;
        }

        $newMonth = $arrayDate[1];
        $newYear = $arrayDate[0];

        for ($i = 0; $i < $nbMonths; $i++) {
            $newMonth += 1;
            if ($newMonth > 12) {
                $newMonth = 1;
                $newYear += 1;
            }
        }

        $newMonth = str_pad($newMonth, 2, 0, STR_PAD_LEFT);

        return $newYear . '-' . $newMonth . '-' . $arrayDate[2]; 
    }
}
      