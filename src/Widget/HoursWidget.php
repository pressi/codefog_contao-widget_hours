<?php

namespace Codefog\WidgetHoursBundle\Widget;

use Contao\Config;
use Contao\Date;
use Contao\StringUtil;
use Contao\Widget;

class HoursWidget extends Widget
{
    /**
     * @inheritdoc
     */
    protected $strTemplate = 'be_hours_widget';

    /**
     * @inheritdoc
     */
    protected $blnSubmitInput = true;

    /**
     * Rows
     */
    protected int $numberOfRows = 1;

    /**
     * Week offset
     */
    protected int $weekOffset = 0;

    /**
     * @inheritdoc
     */
    public function __set($strKey, $varValue)
    {
        switch ($strKey) {
            case 'rows':
                $this->numberOfRows = $varValue;
                break;

            case 'weekOffset':
                $this->weekOffset = $varValue;
                break;

            default:
                parent::__set($strKey, $varValue);
                break;
        }
    }

    /**
     * Validate input and set value
     */
    public function validate()
    {
        $mandatory = $this->mandatory;
        $input = StringUtil::deserialize($this->getPost($this->strName), true);
        $inherit = $this->inheritDays;

        foreach ($input as $dayKey => $day) {
            foreach ($day['rows'] as $rowKey => $row) {
                // @TODO!!
                // Mandatory check
                if ($mandatory && ($row['from'] && $row['to'])) {
                    $mandatory = false;
                }

                if ($row['from'] xor $row['to']) {
                    $mandatory = true;
                }

                // Valid time check
                if (($row['from'] && !preg_match('~^'.Date::getRegexp(Config::get('timeFormat')).'$~i', $row['from'])) || ($row['to'] && !preg_match('~^'.Date::getRegexp(Config::get('timeFormat')).'$~i', $row['to']))) {
                    $this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['time'], Date::getInputFormat(Config::get('timeFormat'))));
                    break 2;
                }

                if (!$this->storeRaw && $row['from'] && $row['to']) {
                    $input[$dayKey]['rows'][$rowkey]['from'] = (new Date($row['from'], Config::get('timeFormat')))->tstamp;
                    $input[$dayKey]['rows'][$rowkey]['to'] = (new Date($row['to'], Config::get('timeFormat')))->tstamp;
                }
            }
        }

        // Throws an error if the field is mandatory
        if ($mandatory) {
            $this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel));
        }

        $this->varValue = $input;
    }

    /**
     * Get the table headers
     */
    public function getTableHeaders(): array
    {
        $headers = [];

        for ($i = 0; $i < 7; $i++) {
            $currentDay = ($i + $this->weekOffset) % 7;
            $headers[] = [
                'label' => $GLOBALS['TL_LANG']['DAYS'][$currentDay],
                'day' => $currentDay,
            ];
        }

        return $headers;
    }

    /**
     * Get the table body
     */
    public function getTableBody(): array
    {
        $body = [];

        for ($j = 0; $j < $this->numberOfRows; $j++) {
            for ($i = 0; $i < 7; $i++) {
                $currentDay = ($i + $this->weekOffset) % 7;

                $body[$j][] = [
                    'from' => [
                        'day' => $currentDay,
                        'id' => $this->strId.'_'.$currentDay.'_'.$j.'_from',
                        'name' => $this->strId.'['.$currentDay.'][rows]['.$j.'][from]',
                        'value' => is_numeric($this->varValue[$currentDay][rows][$j]['from'] ?? false) ? Date::parse(Config::get('timeFormat'), $this->varValue[$currentDay][$j]['from']) : ($this->varValue[$currentDay][rows][$j]['from'] ?? ''),
                    ],
                    'to' => [
                        'day' => $currentDay,
                        'id' => $this->strId.'_'.$currentDay.'_'.$j.'_to',
                        'name' => $this->strId.'['.$currentDay.'][rows]['.$j.'][to]',
                        'value' => is_numeric($this->varValue[$currentDay][rows][$j]['to'] ?? false) ? Date::parse(Config::get('timeFormat'), $this->varValue[$currentDay][$j]['to']) : ($this->varValue[$currentDay][rows][$j]['to'] ?? ''),
                    ],
                ];
            }
        }

        return $body;
    }

    public function getTableFooter(): array
    {
        $footer = [];

        if ($this->showClosed ?? false || $this->inheritDays ?? false)
            for ($j = 0; $j < $this->numberOfRows; $j++) {
                for ($i = 0; $i < 7; $i++) {
                    $currentDay = ($i + $this->weekOffset) % 7;
    
                    $footer[] = [
                        'day' => $currentDay,
                        'showClosedInput' => $this->showClosed ?? false,
                        'showInheritInput' => $this->inheritDays ?? false,
                    ];
                }
            }
        }

        return $footer;
    }

    /**
     * @inheritdoc
     */
    public function parse($attributes = null)
    {
        $this->weekOffset = ($this->weekOffset === null) ? $GLOBALS['TL_LANG']['MSC']['weekOffset'] : $this->weekOffset;

        // Make sure there is at least an empty array
        if (!is_array($this->varValue) || empty($this->varValue)) {
            $this->varValue = [
                [
                    'rows' => [
                        [
                            'from' => '',
                            'to' => '',
                        ],
                    ],
                ],
            ];
        }

        return parent::parse($attributes);
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        return $this->parse();
    }
}
