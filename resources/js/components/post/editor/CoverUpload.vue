<script setup lang="ts">
import {ref} from 'vue'
import {ToastHelper} from '@/helpers'
import {useToast} from 'primevue/usetoast'

const props = defineProps({
    imageSrc: String,
    editable: {
        type: Boolean,
        default: true
    }
})

const emit = defineEmits<{
    (e: 'upload', file: File): void
}>()

const toastHelper = new ToastHelper(useToast())
const imageSrc = ref(props.imageSrc)
const isDraggingOver = ref(false)

const allowedImageFormats = ['PNG', 'JPG', 'JPEG']
const allowedFormats = allowedImageFormats.map((format) => 'image/' + format.toLowerCase())
const maxSizeInMegabytes = 5
const minWidth = 768
const minHeight = 432

function onClick() {
    if (props.editable) {
        const input = document.createElement('input')
        input.type = 'file'
        input.accept = allowedFormats.join(', ')
        input.onchange = () => {
            uploadImage(input.files![0])
        }
        input.click()
    }
}

function onDrop(event: DragEvent) {
    if (props.editable) {
        if (event.dataTransfer?.files) {
            uploadImage(event.dataTransfer.files[0])
        }
        isDraggingOver.value = false
    }
}

function onDragEnter() {
    if (props.editable) {
        isDraggingOver.value = true
    }
}

function onDragLeave() {
    if (props.editable) {
        isDraggingOver.value = false
    }
}

function uploadImage(file: File) {
    if (!allowedFormats.includes(file.type)) {
        toastHelper.error(`Недопустимый формат изображения. Разрешены только ${allowedImageFormats.join(', ')}.`)
        return
    }

    if (file.size > maxSizeInMegabytes * 1024 * 1024) {
        toastHelper.error(`Файл слишком большой. Максимальный размер - ${maxSizeInMegabytes} МБ.`)
        return
    }

    const tempImg = new Image()
    tempImg.onload = () => {
        if (tempImg.width < minWidth || tempImg.height < minHeight) {
            toastHelper.error(`Минимальное разрешение изображения - ${minWidth} x ${minHeight}.`)
            return
        }

        emit('upload', file)
        imageSrc.value = URL.createObjectURL(file)
    }
    tempImg.src = URL.createObjectURL(file)
}
</script>

<template>
    <div
        class="upload-area post-cover relative transition-all h-full flex flex-col justify-center text-center"
        :class="{'editable': editable, 'cursor-pointer': editable, 'empty': !imageSrc, 'dragover': isDraggingOver}"
        @click="onClick"
        @drop.prevent="onDrop"
        @dragover.prevent
        @dragenter.prevent="onDragEnter"
        @dragleave="onDragLeave"
    >
        <img
            v-if="imageSrc"
            :src="imageSrc"
            alt=""
            class="w-full h-full"
            style="border-radius: inherit;"
        />

        <div class="upload-hint absolute left-0 right-0 pointer-events-none">
            <div class="space-x-2 mb-2 text-base sm:text-lg font-medium">
                <span class="fa-solid fa-image"></span>
                <span class="">Загрузить обложку</span>
            </div>

            <p class="text-xs sm:text-sm">Максимальный размер — {{ maxSizeInMegabytes }} МБ.</p>
            <p class="text-xs sm:text-sm">Минимальное разрешение — {{ minWidth }} x {{ minHeight }}.</p>
            <p class="text-xs sm:text-sm">{{ allowedImageFormats.join(' / ') }}</p>
        </div>
    </div>
</template>

<style scoped>
.upload-area.empty {
    background: var(--surface-50);
    border: 2px dashed var(--gray-400);
}

.upload-area.empty:hover, .upload-area.empty.dragover {
    border: 4px dashed var(--primary-color);
}

.upload-area:not(.empty) {
    color: white;

    .upload-hint {
        opacity: 0;
        transition: opacity 0.3s ease;
    }
}

.upload-area.editable:not(.empty):hover, .upload-area:not(.empty).dragover {
    .upload-hint {
        opacity: 1;
    }
}

/* Darkening when hovering/dragging over the cover upload area */
.upload-area.editable::before {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.05);
    border-radius: inherit;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.upload-area.editable:not(.empty)::before {
    background-color: rgba(0, 0, 0, 0.65);
}

.upload-area.editable:hover::before, .upload-area.dragover::before {
    opacity: 1;
}
</style>
