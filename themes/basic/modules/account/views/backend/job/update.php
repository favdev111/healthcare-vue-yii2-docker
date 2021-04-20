<?php

use common\assets\ToastrAsset;
use frontend\assets\NouisliderAsset;
use frontend\assets\TouchPunchAsset;
use kartik\widgets\Select2;
use modules\payment\models\CardInfo;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\jui\SliderInput;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;
use modules\account\widgets\Breadcrumbs;

ToastrAsset::register($this);
NouisliderAsset::register($this);
TouchPunchAsset::register($this);
$this->theme->registerJsFile('account/job.js');
$accountSetting = Yii::$app->getModule('account');

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var modules\account\models\forms\LoginForm $model
 */
$this->title = 'Update Job';
\common\helpers\FormError::show([$job]);

$this->params['breadcrumbs'][] = $this->title;

?>
<div class="applicants-content bg-grey">
    <div class="container">
        <div class="row">
            <?php $form = ActiveForm::begin([
                'id' => 'request-tutor'
            ]); ?>
            <div class="col-xs-9 job-applicants">
                <div id="job-create">

                    <div class="well bg-white well--no-border well--job-preview">
                        <div class="job-step">
                            <div class="job-step-item">
                                <h4 class="job-step-item__title">Subject</h4>
                            </div>
                            <div class="job-step__body">
                                <div class="row">
                                    <div class="col-xs-6">
                                        <div class="form-group">
                                            <!-- input-group -->
                                            <div class="input-group">
                                                <?php echo Select2::widget([
                                                    'name' => 'select-subject',
                                                    'theme' => 'default',
                                                    'options' => ['placeholder' => 'Subject'],
                                                    'pluginOptions' => [
                                                        'allowClear' => true,
                                                        'minimumInputLength' => 1,
                                                        'ajax' => [
                                                            'url' => '/api/auto/subjects/?withCategory=1',
                                                            'data' => new JsExpression('function(params) { return {query:params.term}; }')
                                                        ]
                                                    ],
                                                    'pluginEvents' => [
                                                        "change" => "function() { }",
                                                        "select2:selecting" => "function(el) {
                                                        var choice = el.params.args.data;
                                                        var id = choice.id;
                                                        var exist = false;
                                                        $.each($('.current-subject-block'), function (i, el) {
                                                            if($(el).data('id') == id) {
                                                                exist = true;
                                                                return false;
                                                            }
                                                        });
                                                        if (exist) {
                                                            toastr.error('such subject exists');
                                                            return false;
                                                        }
                                                    }",
                                                        "select2:select" => "function(el) {
                                                        $('#btnGetSearch').trigger('click');
                                                    }"
                                                    ]
                                                ]);
                                                ?>
                                                <!--                                        <input type="text" class="form-control form-control--sm form-control--dark-border form-control--inner-shadow form-control--has-loader" placeholder="Subject">-->
                                                <span class="input-group-btn">
                                                <button class="btn btn-primary btn-sm" id="btnGetSearch" type="button">Add Subject</button>
                                            </span>
                                            </div><!-- /input-group -->
                                        </div>
                                    </div>
                                </div>
                                <div id="subject-current-container">
                                    <?php foreach($curSubjects as $key => $subject) :?>
                                        <span class='current-subject-block label label-subject__item label-subject__item--has-btn-close label-subject__item--lgrey' data-id='<?= $key ?>'>
                                        <?= $subject ?>
                                            <span class="close close--xs"></span>
                                        <input type='hidden' name='Job[subjects][<?= $key ?>]' value="<?= $key ?>">
                                    </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <h4 class="job-step-item__title job-step-item__subtitle--divider">Zip Code</h4>
                            <div class="job-step__body job-step__body--grade">
                                <?= Html::activeTextInput($job, 'zipCode', [ 'onkeypress' => 'checkNumber(event)', 'id' => 'job-zipCode', 'maxlength' => 5, 'class' => 'form-control form-control--job-post-zip', 'label' => false])?>
                            </div>
                        </div>

                        <div class="job-step">
                            <div class="job-step-item">
                                <h4 class="job-step-item__title">What grade is the student in?</h4>
                            </div>
                            <div class="job-step__body job-step__body--grade">
                                <div class="grade-group btn-group" data-toggle="buttons">
                                    <label class="btn btn-default grade-item <?= $job->studentGrade == 1? 'active': '' ?>" data-grade="<?= $job->grade[1] ?>">
                                        <?= Html::radio('Job[studentGrade]', $job->studentGrade == 1? true : false, ['value' => 1]) ?>
                                        <span class="grade-item__icon">
											<img src="<?= $this->theme->getUrl("img/elementary.svg") ?>" alt="...">
											<img src="<?= $this->theme->getUrl("img/elementary-active.svg") ?>" alt="...">
										</span>
                                        <p>Elementary</p>
                                    </label>
                                    <label class="btn btn-default grade-item <?= $job->studentGrade == 2? 'active': '' ?>"  data-grade="<?= $job->grade[2] ?>">
                                        <?= Html::radio('Job[studentGrade]', $job->studentGrade == 2? true : false, ['value' => 2]) ?>
                                        <span class="grade-item__icon">
											<img src="<?= $this->theme->getUrl("img/middle-school.svg") ?>" alt="...">
											<img src="<?= $this->theme->getUrl("img/middle-school-active.svg") ?>" alt="...">
										</span>
                                        <p>Middle school</p>
                                    </label>
                                    <label class="btn btn-default grade-item <?= $job->studentGrade == 3? 'active': '' ?>"  data-grade="<?= $job->grade[3] ?>">
                                        <?= Html::radio('Job[studentGrade]', $job->studentGrade == 3? true : false, ['value' => 3]) ?>
                                        <span class="grade-item__icon">
											<img src="<?= $this->theme->getUrl("img/high-school.svg") ?>" alt="...">
											<img src="<?= $this->theme->getUrl("img/high-school-active.svg") ?>" alt="...">
										</span>
                                        <p>High school</p>
                                    </label>
                                    <label class="btn btn-default grade-item <?= $job->studentGrade == 4? 'active': '' ?>"  data-grade="<?= $job->grade[4] ?>">
                                        <?= Html::radio('Job[studentGrade]', $job->studentGrade == 4? true : false, ['value' => 4]) ?>
                                        <span class="grade-item__icon">
											<img src="<?= $this->theme->getUrl("img/college.svg") ?>" alt="...">
											<img src="<?= $this->theme->getUrl("img/college-active.svg") ?>" alt="...">
										</span>
                                        <p>College</p>
                                    </label>
                                    <label class="btn btn-default grade-item <?= $job->studentGrade == 5? 'active': '' ?>"  data-grade="<?= $job->grade[5] ?>">
                                        <?= Html::radio('Job[studentGrade]', $job->studentGrade == 5? true : false, ['value' => 5]) ?>
                                        <span class="grade-item__icon">
											<img src="<?= $this->theme->getUrl("img/adult.svg") ?>" alt="...">
											<img src="<?= $this->theme->getUrl("img/adult-active.svg") ?>" alt="...">
										</span>
                                        <p>Adult</p>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="job-step">
                            <div class="job-step-item">
                                <h4 class="job-step-item__title">Where would you like lessons to occur?</h4>
                            </div>
                            <div class="job-step__body">
                                <div class="row">
                                    <div class="col-xs-8">
                                        <div class="btn-group btn-group--btn-flexible btn-group--search-wizard-sm occureLesson" data-toggle="buttons">
                                            <?=\common\components\View::getFrontendLessonOccur($job->lessonOccur)?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="job-step">
                            <div class="job-step-item tutor-preferences-title">
                                <h4 class="job-step-item__title">Tutor preferences</h4>
                            </div>
                            <div class="job-step__body tutor-preferences-block">
                                <div class="modal-tutor-pref modal-tutor-pref--job-step-first">
                                    <p class="modal-tutor-pref__label">Gender</p>
                                    <div class="modal-tutor-pref__button-group">
                                        <div class="btn-group flex-between block-tutor-gender" data-toggle="buttons">
                                            <label class="btn btn-default request-tutor-gender <?= $job->gender == 'B'? 'active' : '' ?>">
												<span class="btn__icon--searchbar">
													<svg width="22px" height="22px" viewBox="-1 -1 22 22" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
														<g id="Group-2" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
															<path d="M4.8162724,16.6189953 C4.81617223,15.7459467 4.81857623,14.8728483 4.81386839,13.9997998 C4.81306706,13.8509399 4.89034565,13.6449414 4.60221621,13.6504806 C4.31113186,13.6560697 4.40393628,13.8687053 4.40353562,14.0115768 C4.39832695,15.74939 4.40048053,17.4872531 4.39992962,19.2250663 C4.39972928,19.7407613 4.12001385,20.0429723 3.64126723,20.0484616 C3.1385307,20.0542503 2.81819768,19.7420089 2.81679534,19.2189782 C2.81203743,17.4728312 2.81474193,15.7266843 2.81409084,13.9805373 C2.81399068,13.6532252 2.81298901,13.6523769 2.47928374,13.651828 C2.00364229,13.6509796 1.52785059,13.6445921 1.05240947,13.6544229 C0.827986041,13.6590639 0.782059621,13.5908468 0.845415042,13.3743188 C1.49619792,11.1497542 2.13846663,8.92269452 2.78303917,6.69633345 C2.81769684,6.57666667 2.85545968,6.45769852 2.88490868,6.33673427 C2.90208727,6.26622169 2.98482494,6.16916085 2.85220426,6.13333068 C2.74066867,6.10323932 2.66829825,6.15653546 2.63529333,6.27884708 C2.56397466,6.54348134 2.47768107,6.80402357 2.39965123,7.06686133 C2.08292421,8.13423114 1.76719885,9.20190037 1.450572,10.2692702 C1.39092275,10.4704781 1.31745049,10.664999 1.14140756,10.7981894 C0.893094379,10.9860732 0.576167024,10.9982994 0.32304584,10.832273 C0.0402252377,10.6467346 -0.0697577698,10.3160291 0.0446826546,9.9222964 C0.230141251,9.28404029 0.428371097,8.64942708 0.621792944,8.0134665 C0.890139462,7.13113607 1.16119048,6.24955417 1.42718308,5.3665251 C1.7252791,4.37675405 2.54303982,3.76000603 3.58382164,3.75311945 C4.26806019,3.7485284 4.9523989,3.74827888 5.63668753,3.75321926 C6.6583876,3.76060486 7.50334358,4.38783246 7.80434443,5.36582646 C8.2564968,6.83491337 8.70278941,8.30579678 9.15123561,9.77598155 C9.22480803,10.0171615 9.27489136,10.2590401 9.17267127,10.5060587 C9.05968327,10.7791764 8.79379083,10.9706034 8.51282331,10.958976 C8.18392604,10.9454524 7.95885152,10.7617105 7.8576331,10.4636913 C7.68404426,9.95258737 7.53614816,9.43280038 7.38098999,8.91560833 C7.12791889,8.07205232 6.87770253,7.22764798 6.6262341,6.38364285 C6.61436435,6.34387036 6.59788693,6.30504603 6.58982352,6.264525 C6.56413076,6.13497747 6.47157676,6.10598398 6.3644485,6.13218292 C6.22686957,6.16586727 6.30600125,6.26776867 6.32503291,6.33413933 C6.55812076,7.14875174 6.79626703,7.96196687 7.03276055,8.77558122 C7.47564749,10.2992617 7.91893511,11.8228424 8.36062005,13.3468224 C8.44601214,13.6414482 8.43754806,13.6505804 8.12077095,13.6513289 C7.6451295,13.6524268 7.16913747,13.6619582 6.69389669,13.6472369 C6.46070867,13.6400509 6.396602,13.7221908 6.39765375,13.9488491 C6.405617,15.7115638 6.40446509,17.4743782 6.4005085,19.2371428 C6.39905609,19.8884734 5.79059363,20.2503182 5.2069725,19.9561914 C4.9316644,19.8174119 4.81416889,19.5910531 4.81522064,19.2880935 C4.8183759,18.3984274 4.81637256,17.5087113 4.8162724,16.6189953 Z M4.6204165,0.0596775629 C5.54330214,0.0601765904 6.29855886,0.814207068 6.29069578,1.72727759 C6.28283269,2.64863198 5.53153256,3.38419844 4.60469033,3.3780105 C3.69597827,3.37197226 2.96466139,2.6290202 2.96486172,1.71205726 C2.96501197,0.806721656 3.71400827,0.0591785355 4.6204165,0.0596775629 Z" id="Female" stroke="#ffffff" stroke-width="0.5" fill="#333333"></path>
															<path d="M18.9731575,12.8708582 C18.9731575,14.9127568 18.9721537,16.9546054 18.9745125,18.9965039 C18.9747635,19.226982 18.9396325,19.4464087 18.8142653,19.6414325 C18.5967546,19.9797736 18.1746815,20.1361427 17.7266114,20.023429 C17.3474984,19.9281174 17.1028366,19.6877381 17.0303163,19.2886395 C17.0032153,19.1394713 16.9969419,18.9913533 16.9969419,18.8414351 C16.9970422,16.5662081 16.9986482,14.2909811 16.9926258,12.0157541 C16.9922745,11.8926391 17.0827115,11.6692619 16.8853761,11.6639113 C16.6810144,11.6583606 16.7693938,11.8823878 16.7690926,12.0031025 C16.7634717,14.2949816 16.7646762,16.5869106 16.7655796,18.8787896 C16.7656297,19.0376089 16.7591556,19.194078 16.7188052,19.3493969 C16.5990087,19.8106031 16.245993,20.0516824 15.6968964,20.0415312 C15.2501815,20.0332302 14.9261739,19.7631473 14.8313203,19.3207434 C14.7962396,19.1569235 14.787758,18.9916033 14.7878081,18.824283 C14.7890628,14.7988929 14.7896149,10.7735029 14.7849475,6.74811279 C14.7847969,6.62694804 14.8700647,6.4089715 14.6613368,6.41102175 C14.4732859,6.41287198 14.554338,6.62304756 14.5537859,6.73956175 C14.5477133,8.07302409 14.54952,9.40648643 14.5505238,10.7399488 C14.5506241,10.8821161 14.5445013,11.0224832 14.5088685,11.16115 C14.4110539,11.5419964 14.1085264,11.7312695 13.6632167,11.6878142 C13.3325342,11.6555602 13.074924,11.3791266 13.0740708,11.020983 C13.0698551,9.23746585 13.0587136,7.4537987 13.0787382,5.6705316 C13.0897292,4.69171244 14.0574861,3.77410072 15.0593201,3.7631994 C16.2301841,3.75049785 17.4015499,3.74659738 18.5722633,3.76479959 C19.7751968,3.78345186 20.6810229,4.72021591 20.683181,5.91966193 C20.686142,7.58646485 20.6848371,9.25331778 20.6833817,10.9201707 C20.68293,11.3963787 20.4350562,11.6773129 19.9961202,11.7096668 C19.6010979,11.7388204 19.3065501,11.4839393 19.2366897,11.0436357 C19.2159624,10.9128198 19.2067782,10.7789035 19.2065775,10.6463874 C19.2044194,9.34622909 19.2077318,8.04607081 19.2012075,6.74601253 C19.2006052,6.62549786 19.2867764,6.40542107 19.0761413,6.41207188 C18.893611,6.41787258 18.9774736,6.62849823 18.9771223,6.74521244 C18.9716519,8.78711102 18.9731575,10.8289596 18.9731575,12.8708582 Z M15.2291129,1.70773416 C15.2298658,0.780171235 15.950602,0.0591334541 16.8763524,0.0596835211 C17.8033575,0.060183582 18.5280585,0.798173427 18.5259507,1.73943802 C18.523893,2.65914999 17.7896564,3.39533961 16.8699285,3.38313813 C15.8092753,3.36908642 15.158299,2.48862923 15.2291129,1.70773416 Z" id="Male" stroke="#fff" stroke-width="0.5" fill="#333333"></path>
														</g>
													</svg>
												</span>
                                                <?= Html::radio('Job[gender]', $job->gender == 'B'? true : false, ['value' => 'B']) ?> <span>Both</span>
                                            </label>
                                            <label class="btn btn-default request-tutor-gender <?= $job->gender == 'M'? 'active' : '' ?>">
												<span class="btn__icon--searchbar">
													<svg width="9px" height="22px" viewBox="-1 -1 9 22" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
														<defs></defs>
														<path d="M5.90599533,12.811175 C5.90599533,14.8530736 5.90499159,16.8949221 5.90735038,18.9368207 C5.90760132,19.1672988 5.87247038,19.3867255 5.74710312,19.5817492 C5.52959243,19.9200904 5.10751931,20.0764595 4.65944929,19.9637458 C4.28033629,19.8684342 4.0356744,19.6280549 3.96315411,19.2289563 C3.9360531,19.0797881 3.92977972,18.9316701 3.92977972,18.7817519 C3.9298801,16.5065249 3.93148608,14.2312979 3.92546364,11.9560709 C3.92511233,11.8329559 4.0155494,11.6095787 3.8182139,11.604228 C3.61385222,11.5986774 3.70223162,11.8227046 3.7019305,11.9434193 C3.69630955,14.2352984 3.69751404,16.5272274 3.6984174,18.8191064 C3.69846759,18.9779257 3.69199346,19.1343948 3.65164307,19.2897137 C3.53184657,19.7509198 3.17883084,19.9919992 2.62973429,19.981848 C2.18301932,19.9735469 1.8590117,19.7034641 1.76415817,19.2610602 C1.72907742,19.0972403 1.72059581,18.9319201 1.720646,18.7645998 C1.72190067,14.7392097 1.72245273,10.7138196 1.71778534,6.68842958 C1.71763477,6.56726483 1.80290258,6.34928829 1.59417462,6.35133854 C1.40612373,6.35318877 1.48717582,6.56336436 1.48662377,6.67987854 C1.48055113,8.01334088 1.48235787,9.34680322 1.48336161,10.6802656 C1.48346198,10.8224329 1.47733916,10.9628 1.44170635,11.1014668 C1.34389179,11.4823132 1.04136423,11.6715862 0.596054502,11.628131 C0.265372006,11.595877 0.00776185997,11.3194434 0.00690868006,10.9612998 C0.00269296756,9.17778264 -0.00844855833,7.39411549 0.011576076,5.61084839 C0.0225670408,4.63202923 0.990323995,3.71441752 1.99215796,3.70351619 C3.16302192,3.69081464 4.33438775,3.68691417 5.50510115,3.70511639 C6.70803463,3.72376866 7.61386076,4.6605327 7.61601881,5.85997872 C7.61897984,7.52678164 7.61767498,9.19363457 7.61621955,10.8604875 C7.61576787,11.3366955 7.36789401,11.6176297 6.92895804,11.6499836 C6.53393574,11.6791372 6.23938793,11.4242561 6.16952755,10.9839525 C6.14880029,10.8531366 6.13961606,10.7192203 6.13941532,10.5867042 C6.13725727,9.28654588 6.14056962,7.9863876 6.1340453,6.68632933 C6.13344306,6.56581465 6.21961423,6.34573786 6.00897916,6.35238867 C5.82644885,6.35818938 5.91031142,6.56881502 5.90996011,6.68552923 C5.90448972,8.72742781 5.90599533,10.7692764 5.90599533,12.811175 Z M2.1619508,1.64805095 C2.1627036,0.720488029 2.88343988,-0.000549752451 3.80919027,3.14515877e-07 C4.73619534,0.000500375395 5.46089639,0.73849022 5.45878853,1.67975481 C5.45673086,2.59946678 4.72249427,3.33565641 3.80276633,3.32345492 C2.74211314,3.30940321 2.09113686,2.42894602 2.1619508,1.64805095 Z" id="Male" stroke="#FFFFFF" stroke-width="0.5" fill="#333333" fill-rule="evenodd"></path>
													</svg>
												</span>
                                                <?= Html::radio('Job[gender]', $job->gender == 'M'? true : false, ['value' => 'M']) ?> <span>Male</span>
                                            </label>
                                            <label class="btn btn-default request-tutor-gender <?= $job->gender == 'F'? 'active' : '' ?>">
												<span class="btn__icon--searchbar">
													<svg width="11px" height="22px" viewBox="-1 -1 11 22" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
														<defs></defs>
														<path d="M4.81625223,16.559318 C4.81615206,15.6862694 4.81855606,14.813171 4.81384823,13.9401225 C4.81304689,13.7912626 4.89032548,13.5852641 4.60219605,13.5908033 C4.31111169,13.5963924 4.40391612,13.809028 4.40351545,13.9518995 C4.39830678,15.6897127 4.40046037,17.4275758 4.39990945,19.165389 C4.39970912,19.681084 4.11999368,19.983295 3.64124707,19.9887843 C3.13851053,19.994573 2.81817751,19.6823315 2.81677518,19.1593009 C2.81201726,17.4131539 2.81472176,15.667007 2.81407068,13.92086 C2.81397051,13.5935479 2.81296884,13.5926996 2.47926357,13.5921506 C2.00362212,13.5913023 1.52783042,13.5849147 1.05238931,13.5947456 C0.827965874,13.5993865 0.782039455,13.5311695 0.845394876,13.3146415 C1.49617775,11.0900769 2.13844646,8.8630172 2.78301901,6.63665614 C2.81767668,6.51698935 2.85543951,6.39802121 2.88488851,6.27705696 C2.9020671,6.20654438 2.98480477,6.10948354 2.8521841,6.07365337 C2.7406485,6.04356201 2.66827808,6.09685814 2.63527316,6.21916977 C2.56395449,6.48380403 2.4776609,6.74434626 2.39963106,7.00718402 C2.08290404,8.07455383 1.76717869,9.14222306 1.45055183,10.2095929 C1.39090258,10.4108007 1.31743032,10.6053216 1.1413874,10.7385121 C0.893074212,10.9263959 0.576146857,10.9386221 0.323025674,10.7725956 C0.040205071,10.5870572 -0.0697779364,10.2563517 0.044662488,9.86261909 C0.230121084,9.22436298 0.428350931,8.58974977 0.621772777,7.95378919 C0.890119295,7.07145875 1.16117031,6.18987686 1.42716292,5.30684779 C1.72525894,4.31707674 2.54301966,3.70032872 3.58380148,3.69344214 C4.26804002,3.68885108 4.95237874,3.68860157 5.63666737,3.69354194 C6.65836744,3.70092755 7.50332341,4.32815515 7.80432426,5.30614915 C8.25647663,6.77523606 8.70276924,8.24611947 9.15121544,9.71630424 C9.22478786,9.95748421 9.2748712,10.1993628 9.17265111,10.4463814 C9.0596631,10.7194991 8.79377066,10.9109261 8.51280315,10.8992987 C8.18390587,10.8857751 7.95883136,10.7020332 7.85761293,10.404014 C7.68402409,9.89291005 7.536128,9.37312306 7.38096982,8.85593101 C7.12789872,8.01237501 6.87768237,7.16797066 6.62621393,6.32396554 C6.61434418,6.28419305 6.59786677,6.24536871 6.58980335,6.20484768 C6.5641106,6.07530016 6.47155659,6.04630666 6.36442833,6.0725056 C6.22684941,6.10618996 6.30598108,6.20809136 6.32501275,6.27446201 C6.5581006,7.08907442 6.79624686,7.90228956 7.03274038,8.71590391 C7.47562733,10.2395844 7.91891494,11.7631651 8.36059989,13.2871451 C8.44599197,13.5817709 8.43752789,13.5909031 8.12075079,13.5916516 C7.64510934,13.5927495 7.1691173,13.6022809 6.69387652,13.5875596 C6.46068851,13.5803736 6.39658184,13.6625135 6.39763359,13.8891718 C6.40559684,15.6518864 6.40444492,17.4147009 6.40048834,19.1774655 C6.39903592,19.8287961 5.79057346,20.1906409 5.20695234,19.8965141 C4.93164424,19.7577346 4.81414873,19.5313757 4.81520048,19.2284162 C4.81835573,18.33875 4.8163524,17.449034 4.81625223,16.559318 Z M4.62039633,2.49702088e-07 C5.54328198,0.000499277152 6.2985387,0.754529755 6.29067561,1.66760028 C6.28281253,2.58895466 5.53151239,3.32452112 4.60467016,3.31833318 C3.6959581,3.31229495 2.96464122,2.56934288 2.96484155,1.65237994 C2.9649918,0.747044343 3.7139881,-0.000498777748 4.62039633,2.49702088e-07 Z" id="Female" stroke="#FFFFFF" stroke-width="0.5" fill="#333333" fill-rule="evenodd"></path>
													</svg>
												</span>
                                                <?= Html::radio('Job[gender]', $job->gender == 'F'? true : false, ['value' => 'F']) ?> <span>Female</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-tutor-pref">
                                    <p class="modal-tutor-pref__label">
                                        Hourly rate
                                        <small class="text-grey">From $<?= $accountSetting->hourlyRateMin ?> to $<?= $accountSetting->hourlyRateMax ?></small>
                                    </p>
                                    <div class="form-group form-group--range-job-step">
                                        <div class="slider-group">
                                            <?= Html::activeTextInput($job, 'hourlyRateFrom', [ 'onkeypress' => 'validate(event)', 'id' => 'job-ratefrom', 'class' => 'range__value form-control form-control--range-value', 'maxlength' => 3, 'label' => false])?>
                                            <div class="slider-primary">
                                                <?= SliderInput::widget([
                                                    'name' => 'asd',
                                                    'clientOptions' => [
                                                        'min' => $accountSetting->hourlyRateMin,
                                                        'max' => $accountSetting->hourlyRateMax,
                                                        'range' => true,
                                                        'step' => 1,
                                                        'values'=> [$job->hourlyRateFrom? $job->hourlyRateFrom :$accountSetting->hourlyRateMin, $job->hourlyRateTo? $job->hourlyRateTo :$accountSetting->hourlyRateMax],
                                                        'slide' => new JsExpression('function( event, ui ) {
                                                      var from = ui.values[0];
                                                      var to = ui.values[1];
                                                      $("#job-ratefrom").val(from);
                                                      $("#job-rateto").val(to);
                                                      checkFillTutorPreferencesBlock();
                                                    }'),
                                                        'create' => new JsExpression('function( event, ui ) {
                                                    }')
                                                    ],
                                                ]); ?>
                                            </div>
                                            <?= Html::activeTextInput($job, 'hourlyRateTo', [ 'onkeypress' => 'validate(event)', 'id' => 'job-rateto', 'class' => 'range__value form-control form-control--range-value', 'maxlength' => 3, 'label' => false])?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="job-step">
                            <div class="job-step-item">
                                <h4 class="job-step-item__title">When would you like to start lessons?</h4>
                            </div>
                            <div class="job-step__body job-step__body--grade">
                                <div class="btn-group btn-group--btn-flexible start-lesson-wrapper-job" data-toggle="buttons">
                                    <label class="btn btn-default <?= $job->startLesson == '1'? 'active' : ''?>">
                                        <?= Html::radio('Job[startLesson]', $job->startLesson == '1'? true : false, ['value' => '1']) ?> <span>Today</span>
                                    </label>
                                    <label class="btn btn-default <?= $job->startLesson == '2'? 'active' : ''?>">
                                        <?= Html::radio('Job[startLesson]', $job->startLesson == '2'? true : false, ['value' => '2']) ?> <span>Within a few days</span>
                                    </label>
                                    <label class="btn btn-default <?= $job->startLesson == '3'? 'active' : ''?>">
                                        <?= Html::radio('Job[startLesson]', $job->startLesson == '3'? true : false, ['value' => '3']) ?> <span>Within two weeks</span>
                                    </label>
                                    <label class="btn btn-default <?= $job->startLesson == '4'? 'active' : ''?>">
                                        <?= Html::radio('Job[startLesson]', $job->startLesson == '4'? true : false, ['value' => '4']) ?> <span>This month</span>
                                    </label>
                                </div>
                            </div>
                            <h4 class="job-step-item__subtitle">What is your availability?</h4>
                            <div class="job-step__body job-step__body--table">
                                <table class="table scheduling-table scheduling-table--search-wizard table-responsive well well--container bg-white">
                                    <tbody>
                                    <tr class="scheduling-table__header scheduling-table__header--no-border">
                                        <th colspan="2">
                                        </th>
                                        <th class="text-center">Sunday</th>
                                        <th class="text-center">Monday
                                        </th><th class="text-center">Tuesday</th>
                                        <th class="text-center">Wednesday</th>
                                        <th class="text-center">Thursday</th>
                                        <th class="text-center">Friday</th>
                                        <th class="text-center">Saturday</th>
                                    </tr>
                                    <tr>
                                        <td colspan="9">
                                            <div class=""></div>
                                        </td>
                                    </tr>
                                    <?php
                                    //echo '<pre>';print_r($job->availabilityArray);die;
                                    foreach ($job->getAvailabilityData() as $dayTime => $times) :?>
                                        <tr class="scheduling-table__body">
                                            <?php foreach ($times as $time => $days) :?>
                                                <td class="no-border scheduling-table__day scheduling-table__day--search-wizard">
                                                    <p><?= $dayTime ?></p>
                                                </td>
                                                <td class="no-border scheduling-table__time scheduling-table__time--search-wizard">
                                                    <span><?= $time ?></span>
                                                </td>
                                                <?php foreach ($days as $key => $day) :?>
                                                    <td class="text-center scheduling-table__clock-icon">
                                                        <label>
                                                            <input type="checkbox" name="Job[availabilityArray][<?= $key ?>]" <?= key_exists($key, $job->availabilityArray)? 'checked' : ''?> value="<?= $key ?>">
                                                            <p>
                                                                <span><?= $day ?></span>
                                                                <span><?= $dayTime ?></span>
                                                            </p>
                                                            <span class="glyphicon glyphicon-time"></span>
                                                        </label>
                                                    </td>
                                                <?php endforeach; ?>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr>
                                        <td colspan="9">
                                            <div class=""></div>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h4 class="job-step-item__subtitle job-step-item__subtitle--divider">Description</h4>
                            <div class="job-step__body job-step__body--grade">
                                <?= Html::activeTextarea($job, 'description', ['cols' => "30", 'rows' => "7", 'class' => 'form-control form-control--job-step form-control--inner-shadow form-control--no-resize', 'placeholder' => "Please add any additional information that you want to share with tutors"]) ?>
                            </div>
                        </div>

                        <span class="divider divider--job-step"></span>

                        <div class="btn-block--job-preview">
                            <button class="btn btn-success btn--job-preview btn--text-shadow" id="finish-job-btn">Publish</button>
                        </div>
                    </div>
                </div>

            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
