

    <script>

        document.addEventListener('livewire:initialized', () => {
        @this.on('goto', (event) => {
            postid = (event.test);

            if (postid == 'q') {
                $("#q").focus();
                $("#q").select();
            }
            if (postid == 'p') {
                $("#p").focus();
                $("#p").select();
            }

        });
        });
    </script>


