<?php

function selectEmoticons(): void
{
    $arrayHtmlEntities = array(
        "&#128521;",    // 😉
        "&#128513;",    // 😁
        "&#128514;",    // 😂
        "&#128526;",    // 😎
        "&#128525;",    // 😍
        "&#128542;",    // 😞
        "&#128551;",    // 😧
        "&#128519;",    // 😇
        "&#128077;",    // 👍
        "&#128078;",    // 👎
        "&#128076;",    // 👌
        "&#128161;",    // 💡
        "&#128276;",    // 🔔
        "&#9749;",      // ☕
    );

    echo '<option value="">--</option>';

    foreach ($arrayHtmlEntities as $value)
    {
        echo '<option value="' . $value . '">' . $value . '</option>';
    }
}
