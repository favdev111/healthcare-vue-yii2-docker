<?php
$this->theme->registerJsFile('account/subject-widget.js');
?>
<div class="row-bordered clear-disable">
    <div class="cover-bg hidden"></div>
    <div id="wrapper-categories" class="modal-static__tutoring-subject-content_item column-3-5 list-group list-group__default col-divider scrollbar-mod" data-toggle="buttons">
        <?php foreach ($catSubjRel as $id => $catsubj) :?>
            <label for="" class="btn btn-primary list-group-item list-group__default_item" data-category="<?= $id ?>">
                <input type="radio" autocomplete="off" checked>
                <p>
                    <span><?= $catsubj['title'] ?></span>
                    <?php
                        $count = 0;
                        foreach ($catsubj['subjects'] as $subject) {
                            if (in_array($subject->id, $subjects)) {
                                $count++;
                            }
                        }
                    ?>
                    <span class="badge"><?= $count? $count : "" ?></span>
                </p>
            </label>
        <?php endforeach; ?>
    </div>
    <div id="subjects-wrapper" class="modal-static__tutoring-subject-content_item column-3-5 list-group list-group__default col-divider scrollbar-mod" data-toggle="buttons">
        <?php foreach ($catSubjRel as $id => $catsubj) :?>
                <?php
                    foreach ($catsubj['subjects'] as $subject) :
                        $isSelected = in_array($subject->id, $subjects);
                ?>
                    <div class="list-group-item list-group__default_item-checkbox <?php echo $isSelected ? 'active': ''?>" data-subject="<?= $subject->id; ?>" data-category="<?= $id ?>">
                        <?php if ($isSelected): ?>
                          <input type="hidden" name="TutorForm[subjects][]" value="<?= $subject->id; ?>" />
                        <?php endif; ?>
                        <label class="btn checkbox-primary<?php echo $isSelected ? ' active': ''?>">
                            <input
                                type="checkbox"
                                autocomplete="off"
                                value="<?= $subject->id; ?>"
                                <?php echo $isSelected ? 'checked': ''?>
                            />
                            <span class="checkbox-primary__item"></span>
                            <span class="checkbox-primary__text"><?= $subject->name; ?></span>
                        </label>
                    </div>
                <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
</div>
