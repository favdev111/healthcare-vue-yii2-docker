<?php

/**
 * @var int $jobId
 * @var string $jobName
 * @var string $closeButton
 * @var array $classes
 */

use common\helpers\Url;
use modules\account\models\JobLead;
use yii\bootstrap\Html;

if (Yii::$app->user->isGuest):
    if (!isset($classes)) {
        $classes = [];
    }

    $assets = \common\assets\CommonAsset::register($this);
    $this->registerJsFile(
        $assets->baseUrl . '/js/job-apply.js',
        ['depends' => [\yii\web\JqueryAsset::class]]
    );

    $jobLeadModel = new JobLead();
    $inputParams = ['class' => $classes['form-control'] ?? 'form-control material-style', 'maxlength' => true, 'autocomplete' => 'off'];
?>
    <div class="modal fade <?= $classes['modal'] ?? 'modal--center-page modal--apply-to-job-guest' ?>" id="guestApplyInfo" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content <?= $classes['modal-content'] ?? '' ?>">
                <div class="modal-header modal-header--no-border">
                <span class="<?= $classes['modal-close'] ?? 'btn-close btn-close--tutor-info' ?>" data-dismiss="modal">
                    <img src="<?= $this->theme->getUrl($closeButton ?? 'img/modal-close.svg') ?>" alt="Close">
                </span>
                </div>
                <div class="modal-body text-center">
                    <h3 class="<?= $classes['job-title'] ?? 'modal-body__title modal-body__title--xs mt0' ?>">
                        Apply for this <?= Html::encode($jobName) ?>:
                    </h3>
                    <form id="guestApplyInfoForm" class="apply-guest-form">
                        <input type="hidden" name="jobId" value="<?= $jobId ?>" />
                        <div class="form-group">
                            <?= Html::activeTextInput($jobLeadModel, 'firstName', $inputParams) ?>
                            <label>First Name:</label>
                        </div>
                        <div class="form-group">
                            <?= Html::activeTextInput($jobLeadModel, 'lastName', $inputParams) ?>
                            <label>Last Name:</label>
                        </div>
                        <div class="form-group">
                            <?=
                            \yii\widgets\MaskedInput::widget([
                                'model' => $jobLeadModel,
                                'attribute' => 'phoneNumber',
                                'type' => 'tel',
                                'mask' => '(999) 999-9999',
                                'clientOptions' => [
                                    'removeMaskOnSubmit' => true,
                                    'autoUnmask' => true,
                                    'placeholder' => ' ',
                                ],
                                'options' => $inputParams,
                            ]);
                            ?>
                            <label>Phone Number:</label>
                        </div>
                        <div class="form-group">
                            <?= Html::activeTextInput($jobLeadModel, 'email', $inputParams) ?>
                            <label>Email:</label>
                        </div>

                        <div class="text-center">
                            <button class="<?= $classes['submit-button'] ?? 'btn btn-success btn-lg btn--apply-to-job-modal' ?>" type="submit">
                                Apply
                            </button>
                        </div>
                    </form>

                    <div>
                        <p>Already a tutor? <a href="<?= Url::to(['/account/default/login'])?>">Sign in</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <template id="jobLeadApplyTrack">
        <?php if (YII_ENV_PROD): ?>
        <!-- Begin INDEED conversion code -->
        <script type="text/javascript">
          /* <![CDATA[ */
          var indeed_conversion_id = '599204553086235';
          var indeed_conversion_label = '';
          /* ]]> */
        </script>
        <script type="text/javascript" src="//conv.indeed.com/applyconversion.js">
        </script>
        <noscript>
            <img height=1 width=1 border=0 src="//conv.indeed.com/pagead/conv/599204553086235/?script=0">
        </noscript>
        <!-- End INDEED conversion code -->

        <script type='text/javascript' id='monster_conversion_script'>
          (function() {
            var el = document.getElementById('monster_conversion_script');
            el.setAttribute('data-code', 'zWw6dDAUkfYqHNAfw_d7BRg9JD0nR_N7uRAiHcLnLfTmpyR5sBiVwL5qQfEnSZ2uBnHYZZLTFBfdGJZqF4iRSQGn.4VAX4bgXHpiH3EDPJs-');
            el.setAttribute('data-refcodemin', '4');
            el.setAttribute('data-tracking-api', '//logs2.jobs.com/cloudapi/applycomplete/pixel.gif?j_conv=zWw6dDAUkfYqHNAfw_d7BRg9JD0nR_N7uRAiHcLnLfTmpyR5sBiVwL5qQfEnSZ2uBnHYZZLTFBfdGJZqF4iRSQGn.4VAX4bgXHpiH3EDPJs-');
            el.setAttribute('data-jsonapi', '//logs2.jobs.com/cloudapi/evtinspector/pvcnvcd/zWw6dDAUkfYqHNAfw_d7BRg9JD0nR_N7uRAiHcLnLfTmpyR5sBiVwL5qQfEnSZ2uBnHYZZLTFBfdGJZqF4iRSQGn.4VAX4bgXHpiH3EDPJs-');
            var convScript = document.createElement('script');
            convScript.type = 'text/javascript';
            convScript.async = true;
            convScript.src = '//securemedia.newjobs.com/tracking/scripts/conversion.js?rnd=' + Math.floor(Math.random() * 10000000);
            document.body.appendChild(convScript);
          })();
        </script>
        <noscript>
            <img height='1' width='1' border='0' src='//logs2.jobs.com/cloudapi/applycomplete/pixel.gif?j_conv=zWw6dDAUkfYqHNAfw_d7BRg9JD0nR_N7uRAiHcLnLfTmpyR5sBiVwL5qQfEnSZ2uBnHYZZLTFBfdGJZqF4iRSQGn.4VAX4bgXHpiH3EDPJs-'>
        </noscript>
        <?php endif; ?>
    </template>
<?php endif; ?>
