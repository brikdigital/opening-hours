<?php

namespace brikdigital\craftopeninghours\fields;

use brikdigital\craftopeninghours\data\DayData;
use brikdigital\craftopeninghours\data\FieldData;
use brikdigital\craftopeninghours\data\PeriodData;
use brikdigital\craftopeninghours\assetbundles\OpeningHoursAsset;
use brikdigital\craftopeninghours\OpeningHours;
use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Html;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use yii\base\InvalidConfigException;
use yii\db\Schema;
use craft\i18n\Locale;
use craft\helpers\Cp;
use craft\helpers\DateTimeHelper;

/**
 * Opening Hours field type
 */
class OpeningHoursField extends Field
{

    /**
     * @var array|null The time slots that should be shown in the field
     */
    public $slots;

    public static function displayName(): string
    {
        return Craft::t('opening-hours', 'Opening Hours');
    }

    public static function valueType(): string
    {
        return 'mixed';
    }

    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            // ...
        ]);
    }

    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            // ...
        ]);
    }

    public function getSettingsHtml(): ?string
    {
        $columns = [
            'name' => [
                'heading' => Craft::t('app', 'Name'),
                'type' => 'singleline',
                'autopopulate' => 'handle',
            ],
            'handle' => [
                'heading' => Craft::t('app', 'Handle'),
                'code' => true,
                'type' => 'singleline',
            ],
        ];

        $view = Craft::$app->getView();

        $view->registerJsWithVars(fn($id, $name, $columns) => <<<JS
        new Craft.EditableTable($id, $name, $columns, {
            allowAdd: true,
            allowDelete: true,
            allowReorder: true,
            minRows: 1,
            rowIdPrefix: 'slot'
        });
        JS, [
            $view->namespaceInputId('slots'),
            $view->namespaceInputName('slots'),
            $columns,
        ]);

        return Cp::editableTableFieldHtml([
            'label' => Craft::t('opening-hours', 'Time Slots'),
            'instructions' => Craft::t('opening-hours', 'Define the time slots that authors should be able to fill times in for.'),
            'id' => 'slots',
            'name' => 'slots',
            'cols' => $columns,
            'rows' => $this->slots,
            'allowAdd' => true,
            'allowReorder' => true,
            'allowDelete' => true,
            'addRowLabel' => Craft::t('opening-hours', 'Add a time slot'),
            'initJs' => false,
        ]);
    }
    /**
     * @inheritdoc
     */
    public function getContentColumnType(): array|string
    {
        return Schema::TYPE_TEXT;
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue(mixed $value, ?ElementInterface $element = null): mixed
    {

        if (is_string($value) && !empty($value)) {
            $value = Json::decodeIfJson($value);
        } else if ($value instanceof FieldData) {
            return $value;
        }
        $periodData = [];
        if(isset($value['periodData'])) {
            foreach($value['periodData'] as $period) {

                $data = [];

                for ($day = 0; $day <= 6; $day++) {
                    // Normalize the values and make them accessible from both the slot IDs and the handles
                    $dayData = [];
                    foreach ($this->slots as $slotId => $slot) {
                        $dayData[$slotId] = DateTimeHelper::toDateTime($period['days'][$day][$slotId] ?? null) ?: null;
                        if ($slot['handle']) {
                            $dayData[$slot['handle']] = $dayData[$slotId];
                        }
                    }
                    $data[] = new DayData($day, $dayData);
                }

                $periodData['periodData'][] = new PeriodData(DateTimeHelper::toDateTime($period['from']),DateTimeHelper::toDateTime($period['till']), $data);
            }

        }

//        if (is_string($value) && !empty($value)) {
//            $value = Json::decodeIfJson($value);
//            ksort($value);
//        } elseif ($value === null && $this->isFresh($element) && is_array($this->slots)) {
//            $value = [];
//        }
//
//        $data = [];
//
//        for ($day = 0; $day <= 6; $day++) {
//            // Normalize the values and make them accessible from both the slot IDs and the handles
//            $dayData = [];
//            foreach ($this->slots as $slotId => $slot) {
//                $dayData[$slotId] = DateTimeHelper::toDateTime($value['openingstijdenopeninghours-period-days'][$day][$slotId] ?? null) ?: null;
//                if ($slot['handle']) {
//                    $dayData[$slot['handle']] = $dayData[$slotId];
//                }
//                //var_dump($dayData);
//            }
//            $data[] = new DayData($day, $dayData);
//        }

        //Craft::dd($periodData);

        $fieldData = new FieldData($periodData);

        return $fieldData;
    }

    /**
     * @inheritdoc
     */
    public function serializeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        //Craft::dump($value);
        /** @var FieldData $value */
        $serialized = [];

        foreach ($value['periodData'] as $periodData) {
            $serializedDays = [];
            $days = $periodData instanceof PeriodData ? $periodData : $periodData['days'];
            foreach($days as $day) {
                $serializedDay = [];
                foreach (array_keys($this->slots) as $colId) {
                    $serializedDay[$colId] = parent::serializeValue($day[$colId] ?? null);
                }
                $serializedDays[] = $serializedDay;
            }

            $serializedPeriod = [
                'from' => parent::serializeValue(is_array($periodData) ? $periodData['from'] : $periodData->from),
                'till' => parent::serializeValue(is_array($periodData) ? $periodData['till'] : $periodData->till),
                'days' => $serializedDays
            ];


            $serialized['periodData'][] = $serializedPeriod;
        }
        return $serialized;
    }

    protected function inputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        // JS/CSS modules
        $tagOptions = [
            'depends' => [
                'brikdigital\\craftopeninghours\\assetbundles\\OpeningHoursAsset',
            ],
        ];
        // JS/CSS modules
        try {
            OpeningHours::$view->registerAssetBundle(OpeningHoursAsset::class);
            OpeningHours::$plugin->vite->register('src/js/main.ts', false, $tagOptions, $tagOptions);
        } catch (InvalidConfigException $e) {
            Craft::error($e->getMessage(), __METHOD__);
        }

        $columns = [
            'day' => [
                'heading' => '',
                'type' => 'heading',
            ],
        ];

        foreach ($this->slots as $slotId => $slot) {
            $columns[$slotId] = [
                'heading' => Craft::t('site', $slot['name']),
                'type' => 'time',
            ];
        }

        // Get the day key order per the user's Week Start Day pref
        /** @var User $user */
        $user = Craft::$app->getUser()->getIdentity();
        $startDay = (int)($user->getPreference('weekStartDay') ?? Craft::$app->getConfig()->getGeneral()->defaultWeekStartDay);
        $days = range($startDay, 6, 1);
        if ($startDay !== 0) {
            $days = array_merge($days, range(0, $startDay - 1, -1));
        }

        // Build out the editable table rows, explicitly setting each cell value to an array with a 'value' key
        $locale = Craft::$app->getLocale();
        $periods = [];
        $periodValues = $value['periodData'] ?? [[]];
        foreach ($periodValues as $period) {
            $periodData = ['from' => $period->from ?? null, 'till' => $period->till ?? null];
            $rows = [];
            foreach ($days as $day) {
                $row = [
                    'day' => $locale->getWeekDayName($day, Locale::LENGTH_FULL),
                ];

                $data = $period[(string)$day] ?? [];
                foreach ($this->slots as $slotId => $col) {
                    $row[$slotId] = [
                        'value' => $data[$slotId] ?? null,
                    ];
                }

                $rows[(string)$day] = $row;
            }
            $periodData['rows'] = $rows;
            $periods[] = $periodData;
        }


        $variables = [];
        $variables['name'] = $this->handle;
        $id = Craft::$app->getView()->formatInputId($this->handle);
        $nameSpacedId = Craft::$app->getView()->namespaceInputId($id);
        $variables['id'] = $id;
        $variables['nameSpacedId'] = $nameSpacedId;
        $variables['tableColumns'] = $columns;
        $variables['periodData'] = $periods;

        return Craft::$app->getView()->renderTemplate(
            'opening-hours/OpeningHoursHTML',
            $variables
        );
    }

    public function getElementValidationRules(): array
    {
        return [];
    }

//    protected function searchKeywords(mixed $value, ElementInterface $element): string
//    {
//        return StringHelper::toString($value, ' ');
//    }
//
//    public function getElementConditionRuleType(): array|string|null
//    {
//        return null;
//    }
//
//    public function modifyElementsQuery(ElementQueryInterface $query, mixed $value): void
//    {
//        parent::modifyElementsQuery($query, $value);
//    }
}
