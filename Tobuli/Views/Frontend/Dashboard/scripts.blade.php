<script type="text/javascript">
    function initializeDashboardBlocks() {
        const blocks = '{!! json_encode(array_keys($blocks)) !!}';
        const lazyBlocks = JSON.parse(blocks);

        lazyBlocks.forEach(block => {
            app.dashboard.loadBlockContent(block);
        });

        // Initialize dashboard events
        app.dashboard.initEvents();
    }

    // Call the function on document ready
    $(document).ready(function () {
        initializeDashboardBlocks();
    });

        function printPDF() {
            const element = document.body; // The entire page or specific element
            const opt = {
                margin: 1,
                filename: 'my-page.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'in', format: 'letter', orientation: 'landscape' }
            };
            html2pdf().set(opt).from(element).save();
        }
        function printPDFFs(id) {
            const element = document.getElementById('block_fuel_statistics'); // The entire page or specific element
            const opt = {
                margin: 1,
                filename: 'my-page.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'in', format: 'letter', orientation: 'landscape' }
            };
            html2pdf().set(opt).from(element).save();
        }
            
  
</script>