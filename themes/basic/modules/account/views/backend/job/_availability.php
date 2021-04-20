<?php
/**
 * @var $model \modules\account\models\backend\Job
 */
?>
<div class="row">
    <div class="col-xs-6">
        <table class="table scheduling-table scheduling-table--job-search table-responsive well well--container well--shadow-md bg-white well--no-border">
            <tbody><tr class="scheduling-table__header">
                <th colspan="2">
                    <p class="scheduling-table__title scheduling-table__title--sm-grey">
                        Availability
                    </p>
                </th>
                <th class="text-center text-uppercase">Sun</th>
                <th class="text-center text-uppercase">Mon</th>
                <th class="text-center text-uppercase">Tue</th>
                <th class="text-center text-uppercase">Wed</th>
                <th class="text-center text-uppercase">Thu</th>
                <th class="text-center text-uppercase">Fri</th>
                <th class="text-center text-uppercase">Sat</th>
            </tr>
            <?php foreach ($model->getAvailabilityData() as $dayTime => $times) :?>
                <tr class="scheduling-table__body">
                    <?php foreach ($times as $time => $days) :?>
                        <td colspan="2" class="no-border scheduling-table__day scheduling-table__day--job-preview">
                            <p><?= $dayTime ?></p>
                        </td>
                        <?php foreach ($days as $key => $day) :?>
                            <td class="text-center">
                                <?= key_exists($key, $model->availabilityArray)? '<span class="glyphicon glyphicon-ok text-primary"></span>' : ''?>
                            </td>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody></table>
    </div>
</div>
