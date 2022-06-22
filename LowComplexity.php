<?php

class Reduction 
{
    public function checkDeclinationsForValue($declinations) {
        $validDeclinations = array();
        $nbNights = 0;
       
        foreach ($declinations as $declination) {
            $isAttributNotEligible = !$declination->getDeclination()->getProduitAttributeValeurFromIdAttribut(\Attribut::KEY_STAY_PERIOD) &&
                !$declination->getDeclination()->getProduitAttributeValeurFromIdAttribut(\Attribut::KEY_VISIT);
        
            if ($isAttributNotEligible) {
                continue;
            }  

            $nbNights += $declination->getDeclination()->getNbNightDeclination();
            $validDeclinations[] = $declination;

            if ($nbNights >= ($this->getBoughtNights() + $this->getFreeNights())) {
                $this->setValidDeclinationsForSale($validDeclinations);
                return true;
            }
        
        }

        return false;
    }

    public function calculateValue()
    {
        $value = 0;
        $totalPrice = 0;
        $decrementedValue = $this->getPercentage();

        if($this->getUnit() === self::UNIT_EURO || $this->getUnit() === self::UNIT_PERCENT) return;

        foreach ($this->getValidDeclinationsForSale() as $declination) {
            $declinationSale = 0;
            $currentPrice = $this->getCurrentPriceForDeclination($declination);

            /* @var $declination \LogiCE\Entity\Order\Declination */
            $totalPrice += $currentPrice;

            if ($this->getUnit() === self::UNIT_EURO && !$decrementedValue <= 0) {
                $decrementedValue -= $currentPrice <= $decrementedValue ? $currentPrice : $currentPrice - $decrementedValue;
            }
            
            if ($this->getUnit() === self::UNIT_PERCENT) {
                $declinationSale = ($this->getPercentage() / 100) * $currentPrice;
            }

            $this->updateCurrentPriceForDeclination($declination, $declinationSale);
            $this->addSalesToDeclination($declination, $declinationSale);
        }

        if ($this->getUnit() === self::UNIT_EURO) {
            return $this->getPercentage() - $decrementedValue;
        }
        
        if ($this->getUnit() === self::UNIT_PERCENT) {
            return ($this->getPercentage() / 100) * $totalPrice;
        }
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
      