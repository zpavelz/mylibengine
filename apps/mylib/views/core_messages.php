<?php if (Message::count()): ?>
    <?php foreach (Message::getList() as $type => $messages): ?>
        <div class="core_msg core_msg_<?=$type?>" onclick="$(this).fadeOut('slow')">
            <?php foreach ($messages as $text): ?>
                <p class="msg"><?=$text?></p>
            <?php endforeach ?>
        </div>
    <?php endforeach ?>
<?php endif ?>
