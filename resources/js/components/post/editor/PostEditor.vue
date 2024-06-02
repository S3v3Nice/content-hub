<script setup lang="ts">
import {type PropType, ref} from 'vue'
import type {PostVersion, User} from '@/types'
import Dropdown from 'primevue/dropdown'
import {usePostCategoryStore} from '@/stores/postCategory'
import {Document} from '@tiptap/extension-document'
import {Heading} from '@tiptap/extension-heading'
import {Text} from '@tiptap/extension-text'
import {History} from '@tiptap/extension-history'
import {StarterKit} from '@tiptap/starter-kit'
import {Placeholder} from '@tiptap/extension-placeholder'
import {Underline} from '@tiptap/extension-underline'
import {Link} from '@tiptap/extension-link'
import Textarea from 'primevue/textarea'
import {Blockquote} from '@tiptap/extension-blockquote'
import {Image} from '@tiptap/extension-image'
import CoverUpload from '@/components/post/editor/CoverUpload.vue'
import Editor from '@/components/editor/Editor.vue'
import Avatar from 'primevue/avatar'

defineProps({
    author: {
        type: Object as PropType<User>,
        required: true,
    },
    editable: {
        type: Boolean,
        default: true,
    },
})

const postCategoryStore = usePostCategoryStore()
const errors = ref<string[][]>([])
const postVersion = defineModel<PostVersion>({default: {}})

const titleEditorExtensions = [
    Text,
    History,
    Heading.configure({
        HTMLAttributes: {
            class: 'post-title',
        },
        levels: [1],
    }),
    Document.extend({
        content: 'heading',
    }),
    Placeholder.configure({
        placeholder: 'Название материала',
    }),
]

const contentEditorExtensions = [
    StarterKit.configure({
        heading: false,
        blockquote: false,
    }),
    Heading
        .extend({
            marks: ''
        })
        .configure({
            levels: [1, 2],
        }),
    Blockquote.extend({
        priority: 101,
    }),
    Placeholder.configure({
        placeholder: ({node}) => {
            switch (node.type.name) {
                case 'heading':
                    return 'Заголовок'
                case 'codeBlock':
                    return '// Код'
                default:
                    return 'Какой-нибудь текст...'
            }
        },
    }),
    Underline,
    Link.configure({
        openOnClick: 'whenNotEditable',
    }),
    Image,
]
</script>

<template>
    <div class="grid lg:grid-cols-[1fr,19rem] gap-4">
        <div class="min-w-0">
            <div class="surface-overlay rounded-xl p-4 border">
                <slot name="header">
                    <p class="text-2xl font-semibold">Редактор</p>
                </slot>
            </div>

            <div class="surface-overlay rounded-xl border mt-4">
                <div class="flex gap-2 items-center m-4 lg:mx-10">
                    <Avatar :label="author.username![0]" shape="circle"/>
                    <p class="text-sm">{{ author.username }}</p>
                </div>

                <Editor
                    v-model="postVersion.title"
                    :editable="editable"
                    :extensions="titleEditorExtensions"
                    plain-text
                    without-menus
                />

                <CoverUpload
                    :image-src="postVersion.cover_url"
                    @upload="(file) => postVersion.cover_file = file"
                    :editable="editable"
                    class="m-4 lg:mx-10"
                />

                <Editor
                    v-model="postVersion.content"
                    :editable="editable"
                    :extensions="contentEditorExtensions"
                    editor-class="post-content min-h-[25rem]"
                />
            </div>

            <div class="surface-overlay rounded-xl border p-4 mt-4">
                <div class="space-y-1">
                    <label for="category" :class="{ 'p-error': errors['category'] }">Категория</label>
                    <Dropdown
                        :disabled="!editable"
                        input-id="category"
                        :options="postCategoryStore.categories"
                        option-label="name"
                        option-value="id"
                        v-model="postVersion.category_id"
                        class="w-full"
                        :class="{ 'p-invalid': errors['category'] }"
                        aria-describedby="category-error"
                        autocomplete="category"
                    />
                    <small class="p-error" id="category-error">{{ errors['category_id']?.[0] || '&nbsp;' }}</small>
                </div>

                <div class="space-y-1">
                    <label for="description" :class="{ 'p-error': errors['description'] }">Описание</label>
                    <Textarea
                        :disabled="!editable"
                        id="description"
                        v-model="postVersion.description"
                        rows="3"
                        class="w-full"
                        :class="{ 'p-invalid': errors['description'] }"
                        aria-describedby="description-error"
                    />
                    <small class="p-error" id="description-error">{{ errors['description']?.[0] || '&nbsp;' }}</small>
                </div>
            </div>
        </div>

        <div
            class="lg:sticky lg:right-0 lg:top-[--header-with-margin-height] lg:max-h-[calc(100vh-var(--header-with-margin-height))]"
        >
            <div class="surface-overlay rounded-xl border p-4">
                <slot name="sidebar"/>
            </div>
        </div>
    </div>

</template>

<style scoped>

</style>
