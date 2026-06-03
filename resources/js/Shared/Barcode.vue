<script>
import JsBarcode from 'jsbarcode';
import { defineComponent, h } from 'vue';

export default defineComponent({
  name: 'Barcode',

  props: {
    value: {
      type: String,
      default: undefined,
    },

    options: {
      type: Object,
      default: undefined,
    },

    tag: {
      type: String,
      default: 'canvas',
    },
  },

  watch: {
    $props: {
      deep: true,
      immediate: true,

      handler() {
        if (this.$el) {
          this.generate();
        }
      },
    },
  },

  mounted() {
    this.generate();
  },

  methods: {
    generate() {
      JsBarcode(this.$el, String(this.value), this.options);
    },
  },

  render() {
    return h(this.tag, this.$slots.default);
  },
});
</script>
