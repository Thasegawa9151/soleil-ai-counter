<?php
/**
 * Plugin Name: Soleil AI Counter mark01
 * Description: AIエージェントの訪問回数を記事末尾に表示する最小版。一般公開タイプ
 * Version: 0.1.5β
 * Author: Soleil
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AI Bot判定
 */
function soleil_ai_counter_detect_bot(): string {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

    if ($ua === '') {
        return '';
    }
$bots = [
    'SoleilTestBot'  => 'Soleil Test',
    'GPTBot'         => 'OpenAI',
    'ChatGPT-User'   => 'OpenAI',
    'ClaudeBot'      => 'Anthropic',
    'Claude-User'    => 'Anthropic',
    'PerplexityBot'  => 'Perplexity',
    'Google-Extended'=> 'Google',
    'Applebot'       => 'Apple',
    'Bytespider'     => 'ByteDance',
    'CCBot'          => 'Common Crawl',
];

    foreach ($bots as $needle => $name) {
        if (stripos($ua, $needle) !== false) {
            return $name;
        }
    }

    return '';
}

/**
 * AI Botアクセスをカウント
 */
function soleil_ai_counter_count_visit(): void {
    if (is_admin() || is_user_logged_in()) {
        return;
    }

    if (!is_singular('post')) {
        return;
    }

    $bot_name = soleil_ai_counter_detect_bot();

    if ($bot_name === '') {
        return;
    }

    $post_id = get_queried_object_id();

    if (!$post_id) {
        return;
    }

    $total_key = '_soleil_ai_counter_total';
    $total = (int) get_post_meta($post_id, $total_key, true);
    update_post_meta($post_id, $total_key, $total + 1);

    $bot_key = '_soleil_ai_counter_' . sanitize_key($bot_name);
    $bot_count = (int) get_post_meta($post_id, $bot_key, true);
    update_post_meta($post_id, $bot_key, $bot_count + 1);
}
add_action('wp', 'soleil_ai_counter_count_visit');

/**
 * 記事末尾にカウンターを表示
 */
function soleil_ai_counter_append_to_content(string $content): string {

    if (!is_singular('post') || !in_the_loop() || !is_main_query()) {
        return $content;
    }

    $post_id = get_the_ID();

    $count = (int) get_post_meta(
        $post_id,
        '_soleil_ai_counter_total',
        true
    );

    $html = sprintf(
        '<div class="soleil-ai-counter" style="margin-top:2em;padding:1em;border:1px solid #ddd;border-radius:8px;font-size:0.95em;line-height:1.7;">
        🤖この記事はAIエージェントが %s 回見ました<br>
        <small>※Soleil AI Counter mark1.5β による実験的表示です</small>
        </div>',
        esc_html(number_format_i18n($count))
    );

    return $content . $html;
}
add_filter('the_content', 'soleil_ai_counter_append_to_content');
