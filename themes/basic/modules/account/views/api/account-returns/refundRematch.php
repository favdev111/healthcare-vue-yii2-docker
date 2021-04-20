<?php ?>
<div>
    <?php if ((int)$statistics['totalCount'] > 0): ?>
        <table id="__BVID__350"
               style="width: 740px; background-color: #fff; border: 1px solid #000; border-collapse: collapse; border-spacing: 0;">
            <thead>
            <tr>
                <th style="text-transform: uppercase; padding: 10px; text-align: left; font-weight: 100; border-bottom: 1px solid #000">
                    Description
                </th>
                <th style="text-transform: uppercase; padding: 10px; text-align: center; font-weight: 100; border-bottom: 1px solid #000">
                    Percent %
                </th>
                <th style="text-transform: uppercase; padding: 10px; text-align: right; font-weight: 100; border-bottom: 1px solid #000">
                    Count
                </th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <th>
                </th>
                <th>
                </th>
                <th style="text-align: right; font-weight: 500; padding: 10px; ">
                    <span style="text-transform: uppercase">Total:</span>
                    <?= $statistics['totalCount'] ?>
                </th>
            </tr>
            </tfoot>
            <tbody>
            <?php foreach ($statistics['reasons'] as $reason): ?>
                <?php if ($reason['isActive'] || ($reason['count'] > 0 && !$reason['isActive'])): ?>
                    <tr>
                        <td style="padding: 10px; text-align: left; border-bottom: 1px solid #000; <?= !$reason['isActive'] ? 'color: #919191;' : '' ?>">
                            <?= $reason['description'] ?> <?= !$reason['isActive'] ? '(disabled)' : '' ?></td>
                        <td style="padding: 10px; text-align: center; border-bottom: 1px solid #000; <?= !$reason['isActive'] ? 'color: #919191;' : '' ?>">
                            <?= number_format((float)$reason['percent'], 2, '.', '') ?>
                        </td>
                        <td style="padding: 10px; text-align: right; border-bottom: 1px solid #000; <?= !$reason['isActive'] ? 'color: #919191;' : '' ?>">
                            <?= $reason['count'] ?>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <h3>No data available</h3>
    <?php endif; ?>
</div>
