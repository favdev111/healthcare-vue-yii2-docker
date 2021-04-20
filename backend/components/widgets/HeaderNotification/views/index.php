
<li class="dropdown notifications-menu">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="fa fa-bell-o"></i>
        <span class="label label-warning"><?= $notificationsCount ?></span>
    </a>
    <?php
    $message = \Yii::t(
        'app',
        'You have {count, plural, =0{no notifications} one{# notification} other{# notifications}}',
        [
            'count' => $notificationsCount
        ]
    );
    ?>


    <ul class="dropdown-menu">
        <li class="header"><?= $message ?></li>

        <?php
            if ($notificationsCount) :
        ?>
        <li>
            <!-- inner menu: contains the actual data -->
            <ul class="menu">
                <?= $content ?>
            </ul>
        </li>
        <?php
            endif;
        ?>
    </ul>
</li>