<script>

    document.body.style.cursor = 'wait';

    window.addEventListener('load', () => {
        document.body.style.cursor = 'default';
    });

    document.addEventListener('livewire:init', () => {

        Livewire.hook('request', ({ respond }) => {

            document.body.style.cursor = 'wait';

            respond(() => {
                document.body.style.cursor = 'default';
            });

        });

    });

</script>
