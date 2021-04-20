<?php
$this->theme->registerJsFile('account/subject-widget.js');
?>
<div class="tutor-subjects panel-group panel-group--tutor-subjects" id="subjects-wrapper">
    <?php foreach ($catSubjRel as $id => $catsubj) :?>
        <div class="panel panel--tutor-subjects">
            <div class="panel-heading panel-heading--tutor-subjects">
                <a class="btn-collapse btn-collapse--tutor-subjects" data-toggle="collapse" aria-expanded="false" data-category="<?= $id ?>" href="#subbjectCollapse<?= $id ?>">
                    <?php
                    $count = 0;
                    foreach ($catsubj['subjects'] as $subject) {
                        if (in_array($subject->id, $subjects)) {
                            $count++;
                        }
                    }
                    ?>
                    <p><?= $catsubj['title'] ?></p><span class="badge"><?= $count ? $count : "" ?></span></a>
            </div>
            <div id="subbjectCollapse<?= $id ?>" class="panel-collapse collapse">
                <div class="panel-body panel-body--tutor-subjects">
                    <ul class="list-group list-group--tutor-subjects">
                        <?php
                            foreach ($catsubj['subjects'] as $index => $subject) :
                                $isSelected = in_array($subject->id, $subjects);
                        ?>
                            <li class="list-group-item list-group-item--tutor-subjects <?php echo $isSelected ? 'active': ''?>" data-subject="<?= $subject->id; ?>" data-category="<?= $id ?>">
                                <?php if ($isSelected): ?>
                                    <input type="hidden" name="TutorForm[subjects][]" value="<?= $subject->id; ?>" />
                                <?php endif; ?>
                                <div class="tutor-subject-item" data-subject="<?= $subject->name; ?>"><?= $subject->name; ?>
                                    <div class="btn-group btn-group--tutor-subjects">
                                        <button type="button" class="btn btn-delete btn-delete-subject">
                                            <img src="<?= $this->theme->getUrl('img/icon-delete.svg') ?>" alt="" />
                                        </button>
                                    </div>
                                    <button type="button" class="btn btn-add-subject">+</button>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
