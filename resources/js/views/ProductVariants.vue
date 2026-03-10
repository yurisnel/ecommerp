<template>
  <!-- Header row -->
  <div class="flex justify-between items-center">
    <div>
      <h3 class="text-lg font-semibold text-gray-900">
        Variaciones del Producto
      </h3>
      <p class="text-sm text-gray-500 mt-0.5">
        Agrega variantes con combinaciones de atributos (Color, Talla, etc.)
      </p>
    </div>
    <button
      @click="openNewVariantForm"
      class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium"
    >
      <Icon icon="mdi:plus" class="w-4 h-4" />
      Nueva Variante
    </button>
  </div>
  <div
    class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
  >
    <!-- Variant Form Panel -->
    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div
        v-if="showVariantForm"
        class="bg-white rounded-xl shadow-sm border border-indigo-200 p-6"
      >
        <h4
          class="text-base font-semibold text-gray-900 mb-5 flex items-center gap-2"
        >
          <Icon icon="mdi:tag-multiple" class="w-5 h-5 text-indigo-600" />
          {{ editingVariantId ? "Editar Variante" : "Nueva Variante" }}
        </h4>

        <div class="space-y-5">
          <!-- Attributes -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-3"
              >Atributos</label
            >
            <div
              v-if="loadingAttributes"
              class="flex items-center gap-2 text-sm text-gray-500"
            >
              <span
                class="w-4 h-4 border-2 border-indigo-300 border-t-indigo-600 rounded-full animate-spin"
              ></span>
              Cargando atributos...
            </div>
            <div
              v-else-if="allAttributes.length === 0"
              class="text-sm text-gray-500 italic"
            >
              No hay atributos disponibles. Crea atributos desde el módulo de
              Atributos.
            </div>
            <div
              v-else
              class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4"
            >
              <div
                v-for="attr in allAttributes"
                :key="attr.id"
                class="border border-gray-200 rounded-lg p-3 bg-gray-50"
              >
                <label
                  class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide"
                >
                  {{ attr.name }}
                </label>
                <select
                  v-model="variantForm.attribute_values[attr.id]"
                  class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white"
                >
                  <option :value="null">— Sin seleccionar —</option>
                  <option
                    v-for="val in attr.values"
                    :key="val.id"
                    :value="val.id"
                  >
                    {{ val.value }}
                  </option>
                </select>
              </div>
            </div>
          </div>

          <!-- Divider -->
          <div class="border-t border-gray-200"></div>

          <!-- SKU & Name -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                SKU <span class="text-red-500">*</span>
              </label>
              <input
                v-model="variantForm.sku"
                type="text"
                placeholder="AUTO-GENERADO"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                :class="{ 'border-red-300': variantErrors.sku }"
              />
              <p v-if="variantErrors.sku" class="mt-1 text-xs text-red-600">
                {{ variantErrors.sku }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Nombre <span class="text-red-500">*</span>
              </label>
              <input
                v-model="variantForm.name"
                type="text"
                placeholder="AUTO-GENERADO"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                :class="{ 'border-red-300': variantErrors.name }"
              />
              <p v-if="variantErrors.name" class="mt-1 text-xs text-red-600">
                {{ variantErrors.name }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1"
                >Código de Barras</label
              >
              <input
                v-model="variantForm.barcode"
                type="text"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1"
                >Peso (kg)</label
              >
              <input
                v-model.number="variantForm.weight"
                type="number"
                step="0.01"
                min="0"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm"
              />
            </div>
          </div>

          <!-- Active toggle -->
          <div class="flex items-center gap-3">
            <SwitchGroup as="div" class="flex items-center">
              <Switch
                v-model="variantForm.is_active"
                :class="[
                  variantForm.is_active ? 'bg-indigo-600' : 'bg-gray-200',
                  'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
                ]"
              >
                <span
                  aria-hidden="true"
                  :class="[
                    variantForm.is_active ? 'translate-x-5' : 'translate-x-0',
                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                  ]"
                />
              </Switch>
              <SwitchLabel as="span" class="ml-3 cursor-pointer">
                <span class="text-sm font-medium text-gray-700">{{
                  variantForm.is_active ? "Activa" : "Inactiva"
                }}</span>
              </SwitchLabel>
            </SwitchGroup>
          </div>

          <!-- Actions -->
          <div class="flex justify-end gap-3 pt-2">
            <button
              type="button"
              @click="cancelVariantForm"
              class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm transition-colors"
            >
              Cancelar
            </button>
            <button
              type="button"
              @click="saveVariant"
              :disabled="savingVariant"
              class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 text-sm font-medium transition-colors"
            >
              <span
                v-if="savingVariant"
                class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"
              ></span>
              {{
                savingVariant
                  ? "Guardando..."
                  : editingVariantId
                  ? "Actualizar"
                  : "Guardar Variante"
              }}
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Variants Table -->
    <div
      class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
    >
      <div
        v-if="loadingVariants"
        class="flex items-center justify-center py-12 text-gray-500"
      >
        <span
          class="w-6 h-6 border-2 border-indigo-300 border-t-indigo-600 rounded-full animate-spin mr-3"
        ></span>
        Cargando variantes...
      </div>

      <div
        v-else-if="productVariants.length === 0"
        class="flex flex-col items-center justify-center py-16 text-gray-400"
      >
        <Icon
          icon="mdi:tag-multiple-outline"
          class="w-14 h-14 mb-3 opacity-40"
        />
        <p class="text-base font-medium">Sin variantes todavía</p>
        <p class="text-sm mt-1">
          Haz clic en "Nueva Variante" para agregar la primera.
        </p>
      </div>

      <table v-else class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th
              class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
            >
              Atributos
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
            >
              SKU
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
            >
              Nombre
            </th>
            <th
              class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider text-indigo-600"
            >
              Stock
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider"
            >
              Estado
            </th>
            <th
              class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider"
            >
              Acciones
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
          <tr
            v-for="variant in productVariants"
            :key="variant.id"
            class="hover:bg-gray-50 transition-colors"
          >
            <!-- Attribute Values Badges -->
            <td class="px-6 py-4">
              <div class="flex flex-wrap gap-1.5">
                <template
                  v-if="
                    variant.attribute_values &&
                    variant.attribute_values.length > 0
                  "
                >
                  <span
                    v-for="av in variant.attribute_values"
                    :key="av.id"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"
                  >
                    <span class="text-indigo-500 mr-1"
                      >{{ av.attribute ? av.attribute.name : "" }}:</span
                    >
                    {{ av.value }}
                  </span>
                </template>
                <span v-else class="text-xs text-gray-400 italic"
                  >Sin atributos</span
                >
              </div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 font-mono">
              {{ variant.sku }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-700">{{ variant.name }}</td>
            <td class="px-6 py-4 text-right">
              <span
                class="text-sm font-bold"
                :class="
                  variant.total_stock > 0 ? 'text-gray-900' : 'text-red-500'
                "
              >
                {{ variant.total_stock || 0 }}
              </span>
              <span class="text-[10px] text-gray-400 uppercase ml-1">{{
                variant.product.unit || "Pcs"
              }}</span>
            </td>
            <!-- Status Badge -->
            <td class="px-6 py-4">
              <span
                :class="
                  variant.is_active
                    ? 'bg-green-100 text-green-700'
                    : 'bg-gray-100 text-gray-500'
                "
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
              >
                <span
                  :class="variant.is_active ? 'bg-green-400' : 'bg-gray-400'"
                  class="w-1.5 h-1.5 rounded-full mr-1.5"
                ></span>
                {{ variant.is_active ? "Activa" : "Inactiva" }}
              </span>
            </td>
            <!-- Actions -->
            <td class="px-6 py-4">
              <div class="flex justify-end items-center gap-2">
                <router-link
                  :to="{
                    name: 'InventoryEntry',
                    query: {
                      product_id: props.productId,
                      product_variant_id: variant.id,
                    },
                  }"
                  title="Recibir Stock"
                  class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded-md transition-colors"
                >
                  <Icon icon="mdi:package-variant-plus" class="w-4 h-4" />
                </router-link>
                <button
                  type="button"
                  @click="editVariant(variant)"
                  title="Editar"
                  class="p-1.5 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-md transition-colors"
                >
                  <Icon icon="mdi:pencil" class="w-4 h-4" />
                </button>
                <button
                  type="button"
                  @click="toggleVariantStatus(variant)"
                  :title="variant.is_active ? 'Desactivar' : 'Activar'"
                  class="p-1.5 rounded-md transition-colors"
                  :class="
                    variant.is_active
                      ? 'text-yellow-600 hover:bg-yellow-50'
                      : 'text-green-600 hover:bg-green-50'
                  "
                >
                  <Icon
                    :icon="variant.is_active ? 'mdi:eye-off' : 'mdi:eye'"
                    class="w-4 h-4"
                  />
                </button>
                <button
                  type="button"
                  @click="deleteVariant(variant.id)"
                  title="Eliminar"
                  class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors"
                >
                  <Icon icon="mdi:trash-can-outline" class="w-4 h-4" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive, watch } from "vue";
import { useRoute } from "vue-router";

import api from "../axios";
import swal from "../utils/swal";
import { Switch, SwitchGroup, SwitchLabel } from "@headlessui/vue";
import { Icon } from "@iconify/vue";

const props = defineProps({
  productId: {
    type: [Number, String],
    default: null,
  },
});

const route = useRoute();

const allAttributes = ref([]);
const productVariants = ref([]);
const loadingAttributes = ref(false);
const loadingVariants = ref(false);
const showVariantForm = ref(false);
const editingVariantId = ref(null);
const savingVariant = ref(false);
const variantErrors = ref({});

const variantForm = reactive({
  sku: "",
  name: "",
  barcode: "",
  weight: null,
  is_active: true,
  attribute_values: {}, // { attrId: valueId | null }
});

// Auto-generate SKU and Name when attribute values change
watch(
  () => ({ ...variantForm.attribute_values }),
  () => {
    const selectedValues = Object.entries(variantForm.attribute_values)
      .filter(([, valueId]) => valueId !== null)
      .map(([attrId, valueId]) => {
        const attr = allAttributes.value.find((a) => a.id == attrId);
        const val = attr?.values?.find((v) => v.id == valueId);
        return val?.value ?? "";
      })
      .filter(Boolean);

    if (selectedValues.length > 0) {
      const baseSku = variantForm.sku || "SKU";
      variantForm.sku = (
        baseSku +
        "-" +
        selectedValues.join("-")
      ).toUpperCase();
      variantForm.name =
        (variantForm.name || "") + " - " + selectedValues.join(" / ");
    }
  },
  { deep: true }
);

const fetchAllAttributes = async () => {
  loadingAttributes.value = true;
  try {
    const response = await api.get("/attributes/all");
    allAttributes.value = response.data.data || [];
    // Initialize attribute_values with null for each attribute
    resetAttributeValues();
  } catch (error) {
    console.error("Error fetching attributes:", error);
  } finally {
    loadingAttributes.value = false;
  }
};

const resetAttributeValues = () => {
  const values = {};
  allAttributes.value.forEach((attr) => {
    values[attr.id] = null;
  });
  Object.assign(variantForm.attribute_values, values);
};

const fetchProductVariants = async () => {
  loadingVariants.value = true;
  try {
    const response = await api.get(`/products/${props.productId}/variants`);
    productVariants.value = response.data.data || [];
  } catch (error) {
    console.error("Error fetching variants:", error);
  } finally {
    loadingVariants.value = false;
  }
};

const openNewVariantForm = () => {
  editingVariantId.value = null;
  variantErrors.value = {};
  variantForm.sku = "";
  variantForm.name = "";
  variantForm.barcode = "";
  variantForm.weight = null;
  variantForm.is_active = true;
  resetAttributeValues();
  showVariantForm.value = true;
  // Scroll to the form
  setTimeout(() => {
    document
      .querySelector("[data-variant-form]")
      ?.scrollIntoView({ behavior: "smooth", block: "start" });
  }, 100);
};

const cancelVariantForm = () => {
  showVariantForm.value = false;
  editingVariantId.value = null;
  variantErrors.value = {};
};

const editVariant = (variant) => {
  editingVariantId.value = variant.id;
  variantErrors.value = {};
  variantForm.sku = variant.sku;
  variantForm.name = variant.name;
  variantForm.barcode = variant.barcode || "";
  variantForm.weight = variant.weight || null;
  variantForm.is_active = variant.is_active;

  // Reset then fill attribute values
  resetAttributeValues();
  if (variant.attribute_values) {
    variant.attribute_values.forEach((av) => {
      if (av.attribute) {
        variantForm.attribute_values[av.attribute.id] = av.id;
      }
    });
  }
  showVariantForm.value = true;
};

const saveVariant = async () => {
  savingVariant.value = true;
  variantErrors.value = {};

  // Collect only selected (non-null) attribute value ids
  const selectedAttributeValues = Object.values(
    variantForm.attribute_values
  ).filter((v) => v !== null && v !== undefined);

  const payload = {
    product_id: route.params.id,
    sku: variantForm.sku,
    name: variantForm.name,
    barcode: variantForm.barcode || null,
    weight: variantForm.weight || null,
    is_active: variantForm.is_active,
    attribute_values: selectedAttributeValues,
  };

  try {
    if (editingVariantId.value) {
      await api.put(`/variants/${editingVariantId.value}`, payload);
      swal.success("Variante actualizada correctamente");
    } else {
      await api.post("/variants", payload);
      swal.success("Variante creada correctamente");
    }
    showVariantForm.value = false;
    editingVariantId.value = null;
    await fetchProductVariants();
  } catch (error) {
    if (error.response?.status === 422) {
      const apiErrors = error.response.data.errors || {};
      Object.keys(apiErrors).forEach((key) => {
        variantErrors.value[key] = apiErrors[key][0];
      });
      if (error.response.data.message && !Object.keys(apiErrors).length) {
        swal.error(error.response.data.message);
      }
    } else {
      swal.error("Error al guardar la variante");
    }
  } finally {
    savingVariant.value = false;
  }
};

const toggleVariantStatus = async (variant) => {
  try {
    await api.put(`/variants/${variant.id}`, { is_active: !variant.is_active });
    variant.is_active = !variant.is_active;
  } catch (error) {
    swal.error("Error al cambiar el estado de la variante");
  }
};

const deleteVariant = async (id) => {
  const result = await swal.confirm(
    "Esta acción no se puede deshacer.",
    "¿Eliminar variante?"
  );
  if (!result.isConfirmed) return;

  try {
    await api.delete(`/variants/${id}`);
    productVariants.value = productVariants.value.filter((v) => v.id !== id);
    swal.success("Variante eliminada");
  } catch (error) {
    swal.error("Error al eliminar la variante");
  }
};

// ============================================================

onMounted(() => {
  if (productVariants.value.length === 0 && !loadingVariants.value) {
    fetchProductVariants();
  }
  if (allAttributes.value.length === 0 && !loadingAttributes.value) {
    fetchAllAttributes();
  }
});
</script>

<style>
</style>
