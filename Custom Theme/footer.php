<footer class="site-footer">
    <div class="sls-container site-footer__inner">
        <p>
            &copy; <?php echo esc_html(date_i18n('Y')); ?>
            <?php bloginfo('name'); ?>
        </p>
        <p>
            <?php esc_html_e('Lightweight modular WordPress framework.', 'sls-theme'); ?>
        </p>
    </div>
</footer>

<?php wp_footer(); ?>

</body>
</html>
