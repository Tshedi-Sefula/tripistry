<?php

function analyseSentiment($text) {
    $positiveWords = [
        "good", "great", "excellent", "amazing", "love", "loved",
        "beautiful", "friendly", "perfect", "happy", "best",
        "wonderful", "enjoyed", "recommend"
    ];

    $negativeWords = [
        "bad", "poor", "terrible", "awful", "hate", "hated",
        "dirty", "late", "worst", "disappointed", "boring",
        "expensive", "rude", "problem"
    ];

    $text = strtolower($text);
    $score = 0;

    foreach ($positiveWords as $word) {
        if (strpos($text, $word) !== false) {
            $score++;
        }
    }

    foreach ($negativeWords as $word) {
        if (strpos($text, $word) !== false) {
            $score--;
        }
    }

    if ($score > 0) {
        return "positive";
    } elseif ($score < 0) {
        return "negative";
    }

    return "neutral";
}

?>