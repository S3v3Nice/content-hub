<script setup lang="ts">
import {StarterKit} from '@tiptap/starter-kit'
import {Placeholder} from '@tiptap/extension-placeholder'
import {Underline} from '@tiptap/extension-underline'
import {Link} from '@tiptap/extension-link'
import {Blockquote} from '@tiptap/extension-blockquote'
import {Image} from '@tiptap/extension-image'
import Editor from '@/components/editor/Editor.vue'

const content = defineModel<string>()

const editorExtensions = [
    StarterKit.configure({
        heading: false,
        blockquote: false,
        horizontalRule: false,
    }),
    Blockquote.extend({
        priority: 101,
    }),
    Placeholder.configure({
        placeholder: ({node}) => {
            switch (node.type.name) {
                case 'codeBlock':
                    return '// Код'
                default:
                    return 'Ваш комментарий...'
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
    <Editor
        v-model="content"
        :extensions="editorExtensions"
        editor-class="post-comment-content min-h-[2rem]"
    />
</template>

<style scoped>

</style>
