<script>
    $(document).ready(function () {
        $('.salesCardFilter').click(function (e) {
            e.preventDefault();
            const el = $(this);
            const range = el.data('range');
            $.ajax({
                type: 'GET',
                url: "{{route('dashboard.sales.filter')}}?range="+range,
                async: false,
                success: function (response) {
                    if(response.statusCode && response.statusCode == 200) {
                        var displayVal = response.body.data.response.displayVal;
                        var trend = response.body.data.response.trend;
                        var percentage = trend.label;
                        var color = trend.color;
                        var comparedTo = trend.comparedTo;
                        var text = trend.text;
                        console.log('displayVal '+displayVal);
                        el.find('.displayVal').html(displayVal);
                        el.find('.percentage').html(percentage).css({'color':color});
                        el.find('.text').html(text);
                    }
                } 
            })
        });
    });
</script>