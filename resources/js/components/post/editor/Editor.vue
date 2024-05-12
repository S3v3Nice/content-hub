<script setup lang="ts">
import InputText from 'primevue/inputtext'
import {computed, onUnmounted, reactive, ref} from 'vue'
import type {EditorMenuItem, EditorNodeInfo, PostVersion} from '@/types'
import Dropdown from 'primevue/dropdown'
import Button from 'primevue/button'
import {usePostCategoryStore} from '@/stores/postCategory'
import {BubbleMenu, EditorContent, FloatingMenu, useEditor} from '@tiptap/vue-3'
import {Document} from '@tiptap/extension-document'
import {Heading} from '@tiptap/extension-heading'
import {Text} from '@tiptap/extension-text'
import {History} from '@tiptap/extension-history'
import {StarterKit} from '@tiptap/starter-kit'
import {Placeholder} from '@tiptap/extension-placeholder'
import {Underline} from '@tiptap/extension-underline'
import EditorHorizontalMenu from '@/components/post/editor/EditorHorizontalMenu.vue'
import {Link} from '@tiptap/extension-link'
import OverlayPanel from 'primevue/overlaypanel'
import InputGroup from 'primevue/inputgroup'
import Textarea from 'primevue/textarea'
import EditorVerticalMenu from '@/components/post/editor/EditorVerticalMenu.vue'
import {EditorState} from 'prosemirror-state'
import {Blockquote} from '@tiptap/extension-blockquote'
import {Image} from '@tiptap/extension-image'
import axios, {type AxiosError} from 'axios'
import {getErrorMessageByCode, ToastHelper} from '@/helpers'
import {useToast} from 'primevue/usetoast'
import CoverUpload from '@/components/post/editor/CoverUpload.vue'

defineExpose({
    getPostVersion,
})

const postCategoryStore = usePostCategoryStore()
const toastHelper = new ToastHelper(useToast())
const errors = ref<string[][]>([])
const postVersion = reactive<PostVersion>({})

const titleEditor = useEditor({
    enableInputRules: false,
    editorProps: {
        attributes: {
            class: 'focus:outline-none',
        },
    },
    extensions: [
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
    ],
})

const contentEditor = useEditor({
    editorProps: {
        attributes: {
            class: 'post-content focus:outline-none min-h-[25rem] m-4 lg:ml-10 lg:mr-10',
        },
    },
    extensions: [
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
    ],
})

const nodes: { [key: string]: EditorNodeInfo } = {
    'heading-1': {
        name: 'heading',
        attributes: {level: 1},
        displayName: 'Заголовок 1',
        shortcut: 'Ctrl+Alt+1',
        icon: 'fa-solid fa-heading',
    },
    'heading-2': {
        name: 'heading',
        attributes: {level: 2},
        displayName: 'Заголовок 2',
        shortcut: 'Ctrl+Alt+2',
        icon: 'fa-solid fa-heading',
    },
    'paragraph': {
        name: 'paragraph',
        displayName: 'Абзац',
        shortcut: 'Ctrl+Alt+0',
        icon: 'fa-solid fa-paragraph',
    },
    'codeBlock': {
        name: 'codeBlock',
        displayName: 'Код',
        shortcut: 'Ctrl+Alt+C',
        icon: 'fa-solid fa-code',
    },
    'blockquote': {
        name: 'blockquote',
        displayName: 'Цитата',
        shortcut: 'Ctrl+⇧+B',
        icon: 'fa-solid fa-quote-left',
    },
    'bulletList': {
        name: 'bulletList',
        displayName: 'Список',
        shortcut: 'Ctrl+⇧+8',
        icon: 'fa-solid fa-list-ul',
    },
    'orderedList': {
        name: 'orderedList',
        displayName: 'Нумерованный список',
        shortcut: 'Ctrl+⇧+7',
        icon: 'fa-solid fa-list-ol',
    },
    'horizontalRule': {
        name: 'horizontalRule',
        displayName: 'Разделитель',
        icon: 'fa-solid fa-minus',
    },
    'image': {
        name: 'image',
        displayName: 'Изображение',
        icon: 'fa-solid fa-image',
    },
}

const marks: { [key: string]: EditorNodeInfo } = {
    'bold': {
        name: 'bold',
        displayName: 'Жирный',
        shortcut: 'Ctrl+B',
        icon: 'fa-solid fa-bold',
    },
    'italic': {
        name: 'italic',
        displayName: 'Курсив',
        shortcut: 'Ctrl+I',
        icon: 'fa-solid fa-italic',
    },
    'underline': {
        name: 'underline',
        displayName: 'Подчёркнутый',
        shortcut: 'Ctrl+U',
        icon: 'fa-solid fa-underline',
    },
    'strike': {
        name: 'strike',
        displayName: 'Зачёркнутый',
        shortcut: 'Ctrl+⇧+S',
        icon: 'fa-solid fa-strikethrough',
    },
    'code': {
        name: 'code',
        displayName: 'Код',
        shortcut: 'Ctrl+E',
        icon: 'fa-solid fa-code',
    },
    'link': {
        name: 'link',
        displayName: 'Ссылка',
        icon: 'fa-solid fa-link',
    },
}

const menuItems = computed<EditorMenuItem[]>(() => {
    if (!contentEditor.value) {
        return []
    }

    const items: EditorMenuItem[] = []
    const headingItems = ['heading-1', 'heading-2'].map<EditorMenuItem>((key) => ({
        displayName: nodes[key].displayName,
        icon: nodes[key].icon,
        shortcut: nodes[key].shortcut,
        isVisible: !contentEditor.value?.isActive(nodes[key].name, nodes[key].attributes),
        callback: () => contentEditor.value?.chain().focus().setNode(nodes[key].name, nodes[key].attributes).run(),
    }))
    const blockQuoteItem = {
        displayName: nodes['blockquote'].displayName,
        icon: nodes['blockquote'].icon,
        shortcut: nodes['blockquote'].shortcut,
        isActive: contentEditor.value.isActive(nodes['blockquote'].name),
        isVisible: !contentEditor.value.isActive('bulletList') && !contentEditor.value.isActive('orderedList'),
        callback: () => contentEditor.value?.chain().focus().toggleBlockquote().run(),
    }

    if (isAtEmptyRootParagraph()) {
        items.push(
            {
                displayName: 'Заголовок',
                icon: nodes['heading-1'].icon,
                children: headingItems,
            },
            blockQuoteItem,
            {
                displayName: nodes['bulletList'].displayName,
                icon: nodes['bulletList'].icon,
                shortcut: nodes['bulletList'].shortcut,
                callback: () => contentEditor.value?.chain().focus().toggleBulletList().run(),
            },
            {
                displayName: nodes['orderedList'].displayName,
                icon: nodes['orderedList'].icon,
                shortcut: nodes['orderedList'].shortcut,
                callback: () => contentEditor.value?.chain().focus().toggleOrderedList().run(),
            },
            {
                displayName: nodes['image'].displayName,
                icon: nodes['image'].icon,
                shortcut: nodes['image'].shortcut,
                callback: () => openImageDialog((image) => {
                    uploadImage(image, (imageUrl) => {
                        contentEditor.value?.chain().focus().setImage({src: imageUrl}).run()
                    })
                }),
            },
            {
                displayName: nodes['codeBlock'].displayName,
                icon: nodes['codeBlock'].icon,
                shortcut: nodes['codeBlock'].shortcut,
                callback: () => contentEditor.value?.chain().focus().setCodeBlock().run(),
            },
            {
                displayName: nodes['horizontalRule'].displayName,
                icon: nodes['horizontalRule'].icon,
                shortcut: nodes['horizontalRule'].shortcut,
                callback: () => contentEditor.value?.chain().focus().setHorizontalRule().run(),
            },
        )
    } else if (isAtTextBlock()) {
        if (contentEditor.value.isActive('paragraph')) {
            for (const key of ['bold', 'italic', 'underline', 'strike', 'code']) {
                items.push({
                    displayName: marks[key].displayName,
                    icon: marks[key].icon,
                    shortcut: marks[key].shortcut,
                    isVisible: key === 'code' || !contentEditor.value.isActive(marks['code'].name),
                    isActive: contentEditor.value.isActive(marks[key].name),
                    callback: () => contentEditor.value?.chain().focus().toggleMark(marks[key].name).run(),
                })
            }

            items.push({
                displayName: marks['link'].displayName,
                icon: marks['link'].icon,
                shortcut: marks['link'].shortcut,
                isVisible: !contentEditor.value.isActive(marks['code'].name),
                isActive: contentEditor.value.isActive(marks['link'].name),
                callback: linkOverlayPanel.value?.toggle,
            })

            items.push({isSeparator: true})
        }

        items.push({
            displayName: `Преобразовать "${getCurrentNodeInfo()?.displayName ?? '?'}" в`,
            icon: 'fa-solid fa-text-height',
            children: [
                ...headingItems,
                {
                    displayName: nodes['paragraph'].displayName,
                    icon: nodes['paragraph'].icon,
                    shortcut: nodes['paragraph'].shortcut,
                    isVisible: !contentEditor.value.isActive(nodes['paragraph'].name),
                    callback: () => contentEditor.value?.chain().focus().setParagraph().run(),
                },
                {
                    displayName: nodes['codeBlock'].displayName,
                    icon: nodes['codeBlock'].icon,
                    shortcut: nodes['codeBlock'].shortcut,
                    isVisible: !contentEditor.value.isActive(nodes['codeBlock'].name),
                    callback: () => contentEditor.value?.chain().focus().setCodeBlock().run(),
                },
                {
                    displayName: nodes['bulletList'].displayName,
                    icon: nodes['bulletList'].icon,
                    shortcut: nodes['bulletList'].shortcut,
                    isVisible: !contentEditor.value.isActive(nodes['bulletList'].name),
                    callback: () => contentEditor.value?.chain().focus().toggleBulletList().run(),
                },
                {
                    displayName: nodes['orderedList'].displayName,
                    icon: nodes['orderedList'].icon,
                    shortcut: nodes['orderedList'].shortcut,
                    isVisible: !contentEditor.value.isActive(nodes['orderedList'].name),
                    callback: () => contentEditor.value?.chain().focus().toggleOrderedList().run(),
                },
            ],
        })

        items.push(blockQuoteItem)
    }

    return items
})

const addNodeMenu = ref<typeof EditorVerticalMenu>()

const linkOverlayPanel = ref<OverlayPanel>()
const currentLink = reactive({
    text: '',
    href: ''
})

const selectedText = computed(() => {
    const {view, state} = contentEditor.value!
    const {from, to} = view.state.selection
    return state.doc.textBetween(from, to, '')
})

document.addEventListener('scroll', onScroll)

onUnmounted(() => {
    document.removeEventListener('scroll', onScroll)
})

function onScroll() {
    linkOverlayPanel.value?.hide()
}

function isAtTextBlock(): boolean {
    return contentEditor.value ? contentEditor.value.state.selection.$anchor.parent.isTextblock : false
}

function isAtEmptyRootParagraph(editorState: EditorState | undefined = undefined): boolean {
    if (!editorState) {
        if (!contentEditor.value) {
            return false
        }

        editorState = contentEditor.value.state
    }

    const {$anchor, empty} = editorState.selection
    const isRootDepth = $anchor.depth === 1
    const isEmptyParagraph = $anchor.parent.type.name === 'paragraph' && !$anchor.parent.type.spec.code && !$anchor.parent.textContent

    return empty && isRootDepth && isEmptyParagraph
}

function getCurrentNodeInfo(): EditorNodeInfo | null {
    if (!contentEditor.value) {
        return null
    }

    for (const key in nodes) {
        if (contentEditor.value.isActive(nodes[key].name, nodes[key].attributes)) {
            return nodes[key]
        }
    }

    return null
}

function onLinkOverlayPanelShow() {
    currentLink.text = selectedText.value
    currentLink.href = contentEditor.value?.getAttributes('link').href
}

function openImageDialog(callback: ((file: File) => void)) {
    const input = document.createElement('input')
    input.type = 'file'
    input.accept = 'image/jpeg, image/png, image/jpg'
    input.style.display = 'none'
    input.onchange = () => {
        callback(input.files[0])
    }
    input.click()
}

function uploadImage(image: File, callback: ((url: string) => void)) {
    const formData = new FormData()
    formData.append('image', image)

    axios.post('/api/upload-image', formData).then((response) => {
        if (response.data.success) {
            callback(response.data.image_url)
        } else {
            if (response.data.errors) {
                toastHelper.error(response.data.errors['image'][0])
            }
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    })
}

function setLink() {
    const selection = contentEditor.value!.view!.state.selection

    if (currentLink.href.trim() !== '') {
        contentEditor.value!
            .chain()
            .focus()
            .setLink({href: currentLink.href})
            .insertContentAt({from: selection.from, to: selection.to}, currentLink.text)
            .run()
    }

    linkOverlayPanel.value?.hide()
}

function unsetLink() {
    contentEditor.value!.chain().focus().unsetLink().run()
    linkOverlayPanel.value?.hide()
}

function getPostVersion(): PostVersion {
    postVersion.title = titleEditor.value!.getText().trim()
    postVersion.content = contentEditor.value!.getHTML()
    return postVersion
}
</script>

<template>
    <BubbleMenu
        :editor="contentEditor"
        :tippy-options="{zIndex: 100, maxWidth: 'none'}"
        v-if="contentEditor && menuItems.length > 0"
        :update-delay="0"
        class="hidden lg:block"
    >
        <EditorHorizontalMenu
            :items="menuItems"
            class="rounded border drop-shadow-[0_0px_2px_rgba(0,0,0,0.3)]"
        />
    </BubbleMenu>

    <FloatingMenu
        :editor="contentEditor"
        :tippy-options="{ placement: 'left', offset: [0, 0], zIndex: 100 }"
        v-if="contentEditor"
        :should-show="({state}) => isAtEmptyRootParagraph(state)"
        class="hidden lg:block"
    >
        <Button icon="fa-solid fa-plus" text rounded severity="secondary" @click="addNodeMenu?.toggle"/>
        <EditorVerticalMenu ref="addNodeMenu" title="Добавить" :items="menuItems"/>
    </FloatingMenu>

    <OverlayPanel ref="linkOverlayPanel" @show="onLinkOverlayPanelShow">
        <form class="space-y-2" @submit.prevent="setLink">
            <InputGroup>
                <InputText v-model="currentLink.href" placeholder="https://" class="w-full" autocomplete="off"/>
                <Button icon="fa-solid fa-check" outlined title="Сохранить ссылку" type="submit"/>
                <Button icon="fa-solid fa-xmark" outlined title="Удалить ссылку" severity="danger" @click="unsetLink"/>
            </InputGroup>
            <InputText v-model="currentLink.text" placeholder="Текст..." class="w-full" autocomplete="off"/>
        </form>
    </OverlayPanel>

    <div class="grid lg:grid-cols-[1fr,19rem] gap-4">
        <div class="min-w-0">
            <div class="surface-overlay rounded-xl p-4 border">
                <p class="text-2xl font-semibold flex text-center">Создание материала</p>
            </div>

            <div class="surface-overlay rounded-xl border mt-4">
                <div class="m-4 lg:ml-10 lg:mr-10 space-y-4">
                    <EditorContent :editor="titleEditor"/>
                    <CoverUpload @upload="(file) => postVersion.cover_file = file"/>
                </div>

                <div>
                    <div>
                        <EditorContent :editor="contentEditor"/>
                    </div>

                    <EditorHorizontalMenu
                        v-if="contentEditor"
                        :items="menuItems"
                        class="block lg:hidden sticky bottom-0 rounded-b-xl border-t overflow-x-auto whitespace-nowrap"
                    />
                </div>
            </div>

            <div class="surface-overlay rounded-xl border p-4 mt-4">
                <div class="space-y-1">
                    <label for="category" :class="{ 'p-error': errors['category'] }">Категория</label>
                    <Dropdown
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
                <slot name="actions"/>
            </div>
        </div>
    </div>

</template>

<style scoped>

</style>
