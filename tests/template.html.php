<html><head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Content-Language" content="pl">
    <meta name="description" content=". /nPlan lekcji <?=$table['typeName']; ?> <?=$table['name']; ?>/n utworzony za pomocą programu Plan lekcji Optivum firmy VULCAN">
    <title>Plan lekcji <?=$table['typeName']; ?> - <?=$table['name']; ?></title>
    <link rel="stylesheet" href="../css/plan.css" type="text/css">
    <script language="JavaScript1.2" type="text/javascript" src="../scripts/plan.js"></script>
</head>
<body>
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="tabtytul">
    <tr>
        <td class="tytul">
            <img src="../images/pusty.gif" height="80" width="1">
            <span class="tytulnapis"><?=$table['name']; ?></span></td></tr></table>
<div align="center">
    <table border="0" cellpadding="10" cellspacing="0">
        <tr>
            <td colspan="2">
                <table border="1" cellspacing="0" cellpadding="4" class="tabela">
                    <tr>
                        <th>Nr</th>
                        <th>Godz</th>
                        <?php foreach ((array) $table['days'] as $day): ?>
                            <th><?= $day['name']; ?></th>
                        <?php endforeach; ?>
                    </tr>
                    <?php foreach ((array) $table['days'][0]['hours'] as $i => $hour): ?>
                        <tr>
                            <td class="nr"><?= $hour['number']; ?></td>
                            <td class="g"><?=$hour['start'] < 10 ? ' ' : ''; ?><?= $hour['start']; ?>-<?=$hour['end'] < 10 ? ' ' : ''; ?><?= $hour['end']; ?></td>

                            <?php foreach ((array) $table['days'] as $key => $day): ?>
                                <?php $lessons = $day['hours'][$i]['lessons']; ?>
                                <?php if (count($lessons) > 0): ?>
                                    <td class="l">
                                        <?php foreach ((array) $lessons as $lesson): ?>
                                            <?php if (empty($lessons[0]['subject'])): ?>
                                                <?=$lessons[0]['alt']; ?>
                                                <?php continue; ?>
                                            <?php endif; ?>

                                            <?php if ($lesson['diversion']): ?><span style="font-size:85%"><?php endif; ?>

                                            <?php if ($lesson['teacher']['value'] && empty($lesson['room']['name'])): ?>
                                                <a href="n<?= $lesson['teacher']['value']; ?>.html" class="n"><?= $lesson['teacher']['name']; ?></a>
                                            <?php endif; ?>

                                            <?php if (isset($lesson['className']['value']) && !empty($lesson['className']['value'])): ?>
                                                <a href="o<?=$lesson['className']['value']; ?>.html" class="o"><?=$lesson['className']['name']; ?></a><?=$lesson['alt'] ?? $lesson['alt']; ?>
                                            <?php elseif (isset($lesson['className'][0])): ?>
                                                <?php foreach ((array) $lesson['className'] as $group):
                                                    ?><a href="o<?= $group['value']; ?>.html" class="o"><?=str_replace($group['alt'], '', $group['name']);
                                                    ?></a><?=$group['alt']; ?><?php
                                                    if ($group !== end($lesson['className'])):
                                                        ?>,<?php endif; ?><?php
                                                endforeach; ?>
                                            <?php endif; ?>

                                            <?php if (@strpos($lesson['subject'], $lesson['alt']) !== false): ?>
                                            <?php $subject = explode($lesson['alt'], $lesson['subject']); ?>
                                                <span class="p"><?=$subject[0]; ?></span><?=$lesson['alt']; ?>
                                                <span class="p"><?=trim($subject[1]); ?></span>
                                            <?php elseif ($lesson['subject']): ?>
                                                <span class="p"><?=$lesson['subject']; ?></span>
                                                <?php if (empty($lesson['room']['name'])): ?>
                                                    <br>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                            <?php if ($lesson['teacher']['value'] && isset($lesson['className']['value']) && empty($lesson['className']['value'])): ?>
                                                <a href="n<?= $lesson['teacher']['value']; ?>.html" class="n"><?= $lesson['teacher']['name']; ?></a>
                                            <?php endif; ?>

                                            <?php if ($lesson['room']['name']): ?>
                                                <?php if ($lesson['room']['value']): ?>
                                                    <a href="s<?=$lesson['room']['value']; ?>.html" class="s"><?= $lesson['room']['name']; ?></a>
                                                <?php else: ?>
                                                    <span class="s"><?=$lesson['room']['name']; ?></span>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                            <?php if (is_array($lesson['className']) &&
                                                $table['typeName'] === 'nauczyciela' &&
                                                $lesson === end($lessons)
                                            ): ?><br><?php endif; ?>

                                            <?php if ($lesson['diversion']): ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($lesson !== end($lessons)): ?>
                                                <br>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </td>
                                <?php else: ?>
                                    <td class="l">&nbsp;</td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </td>
        </tr>
        <tr>
            <td align="left"><?=$table['description']; ?></td></tr>
        <tr>
            <td align="left">
                <a href="javascript:window.print()">Drukuj plan</a>
            </td>
            <td class="op" align="right">
                <table border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="right">wygenerowano <?=$table['generated']; ?><br>za pomocą programu<a href="http://www.vulcan.edu.pl/dla_szkol/optivum/plan_lekcji/Strony/wstep.aspx" target="_blank">Plan lekcji Optivum</a><br>firmy <a href="http://www.vulcan.edu.pl/" target="_blank">VULCAN</a></td>
                        <td>
                            <img border="0" src="../images/plan_logo.gif" style="margin-left:10" alt="logo programu Plan lekcji Optivum" width="40" height="40">
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <script type="text/javascript" src="../scripts/powrot.js"></script>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
