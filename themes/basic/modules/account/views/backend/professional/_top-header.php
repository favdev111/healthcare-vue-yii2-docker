<?php
\backend\assets\AppAsset::register($this);
\frontend\assets\ExifAsset::register($this);
\frontend\assets\CroppieAsset::register($this);
?>
<div class="page-header page-header--profile">
    <div class="container account--container">
        <div class="flex-start row">
            <div class="col-sm-3">
                <div class="file-control text-center file-control--about-me text-primary text-underline">
                    <div id="croppie-profile"></div>

                    <img class="img-responsive img-circle center-block" id="profile-avatar" src="<?= $account->largeAvatarUrl; ?>">
                    <label>
                        <p class="text-center text-primary fw-normal">
                            <input class="file-control__input" type="file" id="profileInputFile">
                            <span class="glyphicon glyphicon-camera file-control__image"></span>Change image
                        </p>
                    </label>
                </div>
            </div>
            <div class="col-sm-9">
                <div class="account">
                    <div class="account__header pt15 pb15 flex-between">
                        <h4 class="account__title"><?php echo $account->getDisplayName(); ?></h4>
                        <div class="account__right-block">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>