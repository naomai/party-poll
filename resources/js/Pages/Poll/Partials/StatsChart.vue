<script setup>

import { computed, reactive } from "vue";
import VueApexCharts from "vue3-apexcharts";

const props = defineProps({
  stats: {
    type: Array,
  },
});

const options = reactive({
  chart: {
    id: 'vuechart-example',
    type: props.stats.wide ? 'area' : 'bar',
    toolbar: {
      show: false,
    },
    zoom: {
      enabled: false,
    },
    selection: {
      enabled: false,
    },
  },
  plotOptions: {
    bar: {
      borderRadius: 4,
      borderRadiusApplication: 'end',
      horizontal: !props.stats.wide,
    }
  },
  xaxis: {
    type: 'category',
  }
});

const series = computed(()=>[{
  data: props.stats.options.map(
    (d) => ({x: d[0], y: d[1]})
  )
}]);

</script>

<template>
<div>
  <VueApexCharts width="500" :options="options" :series="series"></VueApexCharts>
</div>
</template>
