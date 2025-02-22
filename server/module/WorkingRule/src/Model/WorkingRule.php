<?php /** @noinspection PhpUnused */
/**
 * azebo2 is an application to print working time tables
 *
 * @author Emanuel Minetti < e . minetti@posteo . de >
 * @link      https://github.com/emanuel-minetti/azebo2
 * @copyright Copyright(c) 2019 - 2020 Emanuel Minetti
 * @license   https://www.gnu.org/licenses/gpl-3.0.en.html GPLv3
 */

namespace WorkingRule\Model;

use ArrayObject;
use DateTime;

class WorkingRule extends ArrayObject
{
    public const TIME_FORMAT = 'H:i:s';
    public const DATE_FORMAT = 'Y-m-d';

    /**
     * @var int the primary key of `WorkingRule`
     */
    public $id;

    /**
     * @var int foreign key to `User
     */
    public $userId;

    /**
     * A number indicating the day of the week. A number between 1 and 7. 1 meaning sunday and 7 meaning saturday.
     *
     * @var int indicating the day of the week
     */
    public $weekday;

    /**
     * @var string indicating which calendar weeks (`all`, `even` or `odd`) this rule is valid for.
     */
    public $calendarWeek;

    /**
     * @var DateTime The time the flex time for this rule starts.
     */
    public $flexTimeBegin;

    /**
     * @var DateTime The time the flex time for this rule ends.
     */
    public $flexTimeEnd;

    /**
     * @var DateTime The time the core time for this rule starts.
     */
    public $coreTimeBegin;

    /**
     * @var DateTime The time the core time for this rule ends.
     */
    public $coreTimeEnd;

    /**
     * @var DateTime The target amount of working time for this rule
     */
    public $target;

    /**
     * @var DateTime The starting date for this rule.
     */
    public $validFrom;

    /**
     * @var DateTime The end date (if any) for this rule.
     */
    public $validTo;

    public function exchangeArray($array)
    {
        $this->id = $array['id'] ?? 0;
        $this->userId = $array['user_id'] ?? 0;
        $this->weekday = $array['weekday'] ?? 0;
        $this->calendarWeek = $array['calendar_week'] ?? 'all';
        $this->flexTimeBegin = !empty($array['flex_time_begin']) ?
            DateTime::createFromFormat(self::TIME_FORMAT, $array['flex_time_begin']) : null;
        $this->flexTimeEnd = !empty($array['flex_time_end']) ?
            DateTime::createFromFormat(self::TIME_FORMAT, $array['flex_time_end']) : null;
        $this->coreTimeBegin = !empty($array['core_time_begin']) ?
            DateTime::createFromFormat(self::TIME_FORMAT, $array['core_time_begin']) : null;
        $this->coreTimeEnd = !empty($array['core_time_end']) ?
            DateTime::createFromFormat(self::TIME_FORMAT, $array['core_time_end']) : null;
        $this->target = !empty($array['target']) ?
            DateTime::createFromFormat(self::TIME_FORMAT, $array['target']) : null;
        $this->validFrom = !empty($array['valid_from']) ?
            DateTime::createFromFormat(self::DATE_FORMAT, $array['valid_from']) : null;
        $this->validTo = !empty($array['valid_to']) ?
            DateTime::createFromFormat(self::DATE_FORMAT, $array['valid_to']) : null;
    }

    public function getArrayCopy()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'weekday' => $this->weekday,
            'calendar_week' => $this->calendarWeek,
            'flex_time_begin' => $this->flexTimeBegin->format(self::TIME_FORMAT),
            'flex_time_end' => $this->flexTimeEnd->format((self::TIME_FORMAT)),
            'core_time_begin' => $this->coreTimeBegin ? $this->coreTimeBegin->format(self::TIME_FORMAT) : null,
            'core_time_end' => $this->coreTimeEnd ? $this->coreTimeEnd->format((self::TIME_FORMAT)) : null,
            'target' => $this->target->format(self::TIME_FORMAT),
            'valid_from' => $this->validFrom->format(self::DATE_FORMAT),
            'valid_to' => $this->validTo ? $this->validTo->format(self::DATE_FORMAT) : null,
        ];
    }

    public function __toString()
    {
        return json_encode($this->getArrayCopy());
    }
}
