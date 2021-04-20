<?php

namespace console\controllers;

use common\helpers\LandingPageHelper;
use common\models\City;
use kartik\mpdf\Pdf as KPdf;
use modules\account\models\Account;
use modules\account\models\Lesson;
use modules\account\models\Subject;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

class SiteController extends Controller
{
    // TODO: Remove this script once executed.
    public function actionStripe($startingAfter = null)
    {
        // Init Stripe API Keys
        $payment = Yii::$app->payment;

        $states = City::find()->asArray()->indexBy('stateNameShort')->select('stateName')->column();
        $shortStates = array_keys($states);

        $perPage = 100;
        do {
            $accounts = \Stripe\Account::all([
                'limit' => $perPage,
                'starting_after' => $startingAfter,
            ]);
            foreach ($accounts->data as $account) {
                echo $account->id . "\n\r";
                /**
                 * @var $localAccount \modules\payment\models\Account
                 */
                $localAccount = \modules\payment\models\Account::find()->andWhere(['paymentAccountId' => $account->id])->one();
                if (!$localAccount) {
                    echo "No account found.\n\r";
                    continue;
                }

                echo $account->email . "\n\r";
                $account->email = $localAccount->tutor->email;

                echo json_encode($account->legal_entity->address) . "\n\r";
                if ($account->legal_entity->address->state) {
                    // Remove whitespace if any
                    $account->legal_entity->address->state = trim($account->legal_entity->address->state);
                    $account->legal_entity->address->city = trim($account->legal_entity->address->city);

                    $state = $account->legal_entity->address->state;
                    $city = $account->legal_entity->address->city;
                    if (!in_array($state, $states) && !in_array($state, $shortStates)) {
                        $key = array_search($city, $states);
                        if (in_array($city, $shortStates)) {
                            $account->legal_entity->address->city = $account->legal_entity->address->state;
                            $account->legal_entity->address->state = $city;
                        } elseif ($key !== false) {
                            $account->legal_entity->address->city = $account->legal_entity->address->state;
                            $account->legal_entity->address->state = $key;
                        }
                    }
                    if (!in_array($state, $shortStates)) {
                        $key = array_search($state, $states);
                        if ($key !== false) {
                            // Replace full state name with short one
                            $account->legal_entity->address->state = $key;
                        }
                    }
                }

                $account->save();
                echo "Changed data:\n\r";
                echo $account->email . "\n\r";
                echo json_encode($account->legal_entity->address) . "\n\r";
                $startingAfter = $account->id;
            }
        } while ($accounts->has_more);
    }

    public function actionCreateLessonPdf()
    {
        Console::output('Preparing request...');
        $query = Lesson::find();
        //look for lesson related to company students
        $query->joinWith('student');
        $query->with('subject');
        $query->with('tutor');
        $query->orderBy('createdAt DESC');

        $viewPath = '@themes/basic/modules/account/views/api/lesson/lessonsTable';
        Console::output('Creating file content...');
        $content =  $this->renderPartial(
            $viewPath,
            [
                'lessonsQuery' => $query,
                'companyName' => 'Winit',
                'avatarPath' => '',
            ]
        );
        Console::output('Writing to file...');

        $date = date('Y-m-d');
        $path = realpath('./uploads/lessons/') . "/Lessons-$date.pdf";
        $preparedPdf = new KPDF([
            'mode' => KPDF::MODE_CORE,
            'format' => KPDF::FORMAT_A4,
            'orientation' => KPDF::ORIENT_PORTRAIT,
            'destination' => KPDF::DEST_FILE,
            'filename' => $path,
            'content' => $content,
            'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
            'cssInline' => '.kv-heading-1{font-size:18px}',
            'methods' => [
                'SetTitle' => ['Lessons'],
            ],
        ]);
        $preparedPdf->render();
        Console::output("Done. File $path created");
    }

    public function actionCreateLessonCsv()
    {
        Console::output('Preparing request...');
        $query = Lesson::find();
        //look for lesson related to company students
        $query->joinWith('student');
        $query->with('student.profile.city');
        $query->with('subject');
        $query->with('tutor');
        $query->orderBy('createdAt DESC');

        Console::output('Writing to file...');
        $date = date('Y-m-d');
        $path = realpath('./uploads/lessons/') . "/Lessons-$date.csv";
        $file = fopen($path, 'w+');
        fputcsv(
            $file,
            [
                'ID',
                'STUDENT NAME',
                'TUTOR NAME',
                'SUBJECT',
                'FROM DATE',
                'TO DATE',
                'LESSON DURATION',
                'TOTAL LESSON AMOUNT',
                'TOTAL PAYED TO TUTOR',
                'STATE',
                'CITY',
            ]
        );
        $i = 0;
        $totalCount = (clone $query)->count();
        Console::startProgress($i, $totalCount, 'Progress:');
        /**
         * @var $lesson Lesson
         */
        foreach ($query->each(50) as $lesson) {
            $hours = $lesson->minutesDuration / 60;
            fputcsv(
                $file,
                [
                    $lesson->id,
                    $lesson->student->getFullName(),
                    $lesson->tutor->getFullName(),
                    $lesson->subject->name,
                    Yii::$app->formatter->asDatetime($lesson->fromDate, 'php:m/d/Y g:i A'),
                    Yii::$app->formatter->asDatetime($lesson->toDate, 'php:m/d/Y g:i A'),
                    date("H:i", ($lesson->minutesDuration * 60)) . " " . ($hours == 1 ? "hour" : "hours"),
                    Yii::$app->formatter->priceFormat($lesson->clientPrice),
                    Yii::$app->formatter->priceFormat($lesson->amount + $lesson->fee),
                    $lesson->student->profile->city->stateName ?? '',
                    $lesson->student->profile->city->name ?? '',
                ]
            );
            $i++;
            Console::updateProgress($i, $totalCount, 'Progress:');
        }
        Console::endProgress();
        fclose($file);
        Console::output("Done. File $path created");
    }

    public function actionSetCallUsButtonSubjects(...$subjectIds)
    {
        Subject::setCallUsButtonSubjects($subjectIds);
    }
}
