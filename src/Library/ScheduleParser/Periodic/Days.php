<?php
namespace CroudTech\RecurringTaskScheduler\Library\ScheduleParser\Periodic;

use CroudTech\RecurringTaskScheduler\Contracts\ScheduleParserContract;
use CroudTech\RecurringTaskScheduler\Library\ScheduleParser\Base;

class Days extends Base implements ScheduleParserContract
{
    /**
     * Return generated dates from provided schedule definition
     *
     * @return array
     */
    public function getDates() : array
    {
        if (empty($this->generated)) {
            $interval = $this->getInterval();
            $current_date = $this->getStartDate();
            $original_current_date = $current_date->copy();

            // Prevent iteration over more than 3650 days to stop incorrect definition from causing infinite loops
            while ($current_date->lte($this->getRangeEnd()) && count($this->generated) < 3650) {
                $times = $this->getTimes() ? $this->validateTimes($this->getTimes()) : [$current_date->toTimeString()];
                if ($current_date->lte($this->getRangeEnd()) && $current_date->gte($this->getRangeStart())) {
                    foreach ($times as $time) {
                        $current_date->setTime(...explode(':', $time));
                        $this->generated[] = $current_date->copy();
                    }
                }

                $modify_method = sprintf('add%s', ucfirst(camel_case($this->definition['period'])));
                $current_date->addDays($interval)->setTime(...explode(':', $this->getTimeOfDay()));
            }
        }

        $this->generated = $this->filterExceptions($this->generated);
        $this->sortDates();
        $this->fixTimezones();

        return $this->generated;
    }
}
