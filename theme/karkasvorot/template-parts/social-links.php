            <div class="kv-socials">
                <?php if ( kv_opt( 'viber' ) ) : ?>
                <a class="kv-social kv-social--viber" href="<?php echo esc_url( kv_opt( 'viber' ), array( 'viber', 'https' ) ); ?>" aria-label="Написать в Viber" title="Viber" data-goal="viber_click">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" aria-hidden="true"><path d="M19.4 4.4C17.9 3 6.1 3 4.6 4.4c-2 1.8-2.1 10.5.2 12.2l1.9 1.1v3l3.1-2.8c3 .1 7.8-.2 9.6-1.7 2-1.8 2-10 0-11.8Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M8.1 7.2c-.5-.3-1.6.6-1.5 1.4.4 3.9 3.9 7.2 7.3 7.5.8 0 1.8-1.2 1.3-1.7l-1.6-1.2c-.4-.3-.9.4-1.3.5-1.4-.5-2.8-1.8-3.3-3.2.1-.5.8-.9.5-1.4L8.1 7.2Z" fill="currentColor"/><path d="M12.4 6.5a5 5 0 0 1 5 5m-5-2.6a2.6 2.6 0 0 1 2.6 2.6" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
                </a>
                <?php endif; ?>
                <?php if ( kv_telegram_href() ) : ?>
                <a class="kv-social kv-social--telegram" href="<?php echo esc_url( kv_telegram_href() ); ?>" aria-label="Написать в Telegram" title="Telegram" data-goal="telegram_click" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path fill="currentColor" d="m20.7 4-3 15.1c-.2 1.1-.8 1.3-1.6.8l-4.6-3.4-2.2 2.1c-.2.3-.4.5-.9.5l.3-4.7L17.3 7c.4-.3-.1-.5-.6-.2L6.2 13.4l-4.5-1.5c-1-.3-1-1 .2-1.4L19.4 3.7c.8-.3 1.5.2 1.3.3Z"/></svg>
                </a>
                <?php endif; ?>
            </div>
