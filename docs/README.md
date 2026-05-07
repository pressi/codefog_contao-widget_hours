# Widget Hours – Documentation

## Installation

Install the bundle using Contao Manager or directly via Composer:

```
composer require codefog/contao-widget_hours
```

## Usage example

The following options can be set in the evaluation array:

Property | Type | Description
--- | --- | ---
rows | int |Number of input rows
weekOffset | int | Starting day of the week (0=Sunday, 1=Monday, etc.)
showClosed | bool | Shows a checkbox to mark the day as closed
inheritDays | bool | Shows a inherit checkbox and a select field to choose from where to inherit

For example implementation see below code:

```php
$GLOBALS['TL_DCA']['tl_table']['fields']['openHours'] = [
    'exclude' => true,
    'inputType' => 'hoursWidget',
    'eval' => [
        'mandatory' => true, 
        'rows' => 2, // Number of rows
        'weekOffset' => 1, // Custom week offset
        'showClosed' => true, // true|false (shows a checkbox to mark the day as closed)
        'inherhitDays' => true, // true|false (shows a checkbox and select field to inherit)
        'storeRaw' => true, // Store raw values and do not convert value to timestamp
        'tl_class' => 'clr',
    ],
    'sql' => ['type' => 'blob', 'notnull' => false],
];
```
