<div class="chart-area">
    <canvas id="{{ $chartId }}"></canvas>
</div>

@push('scripts')
<script>
new Chart(document.getElementById('{{ $chartId }}'), {
    type: '{{ $type }}',
    data: {
        labels: {!! json_encode($labels) !!},
        datasets: {!! json_encode($datasets) !!}
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value, index, values) {
                        if (window.currency) {
                            return '฿' + value.toLocaleString('th-TH');
                        }
                        return value;
                    }
                }
            }
        },
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (window.currency) {
                            label += '฿' + context.parsed.y.toLocaleString('th-TH');
                        } else {
                            label += context.parsed.y;
                        }
                        return label;
                    }
                }
            }
        }
    }
});
</script>
@endpush