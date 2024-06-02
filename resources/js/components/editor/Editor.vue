<script setup lang="ts">
import InputText from 'primevue/inputtext'
import {computed, onUnmounted, type PropType, reactive, ref, watch} from 'vue'
import type {EditorMenuItem} from '@/components/editor/types'
import Button from 'primevue/button'
import {BubbleMenu, EditorContent, type Extensions, FloatingMenu, useEditor} from '@tiptap/vue-3'
import EditorHorizontalMenu from '@/components/editor/EditorHorizontalMenu.vue'
import OverlayPanel from 'primevue/overlaypanel'
import InputGroup from 'primevue/inputgroup'
import EditorVerticalMenu from '@/components/editor/EditorVerticalMenu.vue'
import {EditorState} from 'prosemirror-state'
import axios, {type AxiosError} from 'axios'
import {getErrorMessageByCode, ToastHelper} from '@/helpers'
import {useToast} from 'primevue/usetoast'

interface EditorNodeInfo {
    name: string
    attributes?: object
    displayName: string
    shortcut?: string
    icon: string
    callback: (event: Event) => void
}

const props = defineProps({
    editable: {
        type: Boolean,
        default: true
    },
    editorClass: String,
    extensions: Object as PropType<Extensions>,
    plainText: {
        type: Boolean,
        default: false
    },
    withoutMenus: {
        type: Boolean,
        default: false
    },
})

const toastHelper = new ToastHelper(useToast())
const contentModel = defineModel<string>()
let currentEditorContent = contentModel.value
const addNodeMenu = ref<InstanceType<typeof EditorVerticalMenu>>()
const linkOverlayPanel = ref<OverlayPanel>()
const currentLink = reactive({
    text: '',
    href: ''
})

const editor = useEditor({
    editorProps: {
        attributes: {
            class: `focus:outline-none m-4 lg:mx-10 ${props.editorClass}`
        }
    },
    editable: props.editable,
    enableInputRules: false,
    extensions: props.extensions,
    content: contentModel.value,
    onUpdate: ({editor}) => {
        currentEditorContent = props.plainText ? editor.getText() : editor.getHTML()
        contentModel.value = currentEditorContent
    },
})

const nodes: { [key: string]: EditorNodeInfo } = {
    'heading-1': {
        name: 'heading',
        attributes: {level: 1},
        displayName: 'Заголовок 1',
        shortcut: 'Ctrl+Alt+1',
        icon: 'fa-solid fa-heading',
        callback: () => editor.value?.chain().focus().setHeading({level: 1}).run(),
    },
    'heading-2': {
        name: 'heading',
        attributes: {level: 2},
        displayName: 'Заголовок 2',
        shortcut: 'Ctrl+Alt+2',
        icon: 'fa-solid fa-heading',
        callback: () => editor.value?.chain().focus().setHeading({level: 2}).run(),
    },
    'bulletList': {
        name: 'bulletList',
        displayName: 'Список',
        shortcut: 'Ctrl+⇧+8',
        icon: 'fa-solid fa-list-ul',
        callback: () => editor.value?.chain().focus().toggleBulletList().run(),
    },
    'orderedList': {
        name: 'orderedList',
        displayName: 'Нумерованный список',
        shortcut: 'Ctrl+⇧+7',
        icon: 'fa-solid fa-list-ol',
        callback: () => editor.value?.chain().focus().toggleOrderedList().run(),
    },
    'paragraph': {
        name: 'paragraph',
        displayName: 'Абзац',
        shortcut: 'Ctrl+Alt+0',
        icon: 'fa-solid fa-paragraph',
        callback: () => editor.value?.chain().focus().setParagraph().run(),
    },

    'codeBlock': {
        name: 'codeBlock',
        displayName: 'Код',
        shortcut: 'Ctrl+Alt+C',
        icon: 'fa-solid fa-code',
        callback: () => editor.value?.chain().focus().setCodeBlock().run(),
    },
    'blockquote': {
        name: 'blockquote',
        displayName: 'Цитата',
        shortcut: 'Ctrl+⇧+B',
        icon: 'fa-solid fa-quote-left',
        callback: () => editor.value?.chain().focus().toggleBlockquote().run(),
    },
    'horizontalRule': {
        name: 'horizontalRule',
        displayName: 'Разделитель',
        icon: 'fa-solid fa-minus',
        callback: () => editor.value?.chain().focus().setHorizontalRule().run(),
    },
    'image': {
        name: 'image',
        displayName: 'Изображение',
        icon: 'fa-solid fa-image',
        callback: () => openImageDialog((image) => {
            uploadImage(image, (imageUrl) => {
                editor.value?.chain().focus().setImage({src: imageUrl}).run()
            })
        }),
    },
}

const marks: { [key: string]: EditorNodeInfo } = {
    'bold': {
        name: 'bold',
        displayName: 'Жирный',
        shortcut: 'Ctrl+B',
        icon: 'fa-solid fa-bold',
        callback: () => editor.value?.chain().focus().toggleBold().run(),
    },
    'italic': {
        name: 'italic',
        displayName: 'Курсив',
        shortcut: 'Ctrl+I',
        icon: 'fa-solid fa-italic',
        callback: () => editor.value?.chain().focus().toggleItalic().run(),
    },
    'underline': {
        name: 'underline',
        displayName: 'Подчёркнутый',
        shortcut: 'Ctrl+U',
        icon: 'fa-solid fa-underline',
        callback: () => editor.value?.chain().focus().toggleUnderline().run(),
    },
    'strike': {
        name: 'strike',
        displayName: 'Зачёркнутый',
        shortcut: 'Ctrl+⇧+S',
        icon: 'fa-solid fa-strikethrough',
        callback: () => editor.value?.chain().focus().toggleStrike().run(),
    },
    'code': {
        name: 'code',
        displayName: 'Код',
        shortcut: 'Ctrl+E',
        icon: 'fa-solid fa-code',
        callback: () => editor.value?.chain().focus().toggleCode().run(),
    },
    'link': {
        name: 'link',
        displayName: 'Ссылка',
        icon: 'fa-solid fa-link',
        callback: (event: Event) => linkOverlayPanel.value!.toggle(event),
    },
}

const menuItems = computed<EditorMenuItem[]>(() => {
    if (!editor.value) {
        return []
    }

    const items: EditorMenuItem[] = []
    const headingItems = ['heading-1', 'heading-2'].map<EditorMenuItem>((key) => ({
        displayName: nodes[key].displayName,
        icon: nodes[key].icon,
        shortcut: nodes[key].shortcut,
        isVisible: !!(editor.value!.schema.nodes['heading'] && !editor.value?.isActive(nodes[key].name, nodes[key].attributes)),
        callback: nodes[key].callback,
    }))

    if (isAtEmptyRootParagraph()) {
        if (editor.value.schema.nodes['heading']) {
            items.push({
                displayName: 'Заголовок',
                icon: nodes['heading-1'].icon,
                children: headingItems,
            })
        }

        ['blockquote', 'bulletList', 'orderedList', 'image', 'codeBlock', 'horizontalRule'].forEach((key) => {
            if (editor.value!.schema.nodes[key]) {
                items.push({
                    displayName: nodes[key].displayName,
                    icon: nodes[key].icon,
                    shortcut: nodes[key].shortcut,
                    callback: nodes[key].callback,
                })
            }
        })
    } else if (isAtTextBlock()) {
        if (editor.value.isActive('paragraph')) {
            ['bold', 'italic', 'underline', 'strike', 'code', 'link'].forEach((key) => {
                if (editor.value!.schema.marks[key]
                    && (key === 'code' || !editor.value!.isActive(marks['code'].name))
                ) {
                    items.push({
                        displayName: marks[key].displayName,
                        icon: marks[key].icon,
                        shortcut: marks[key].shortcut,
                        isActive: editor.value!.isActive(marks[key].name),
                        callback: marks[key].callback,
                    })
                }
            })

            items.push({isSeparator: true})
        }

        const currentNodeInfo = getCurrentNodeInfo()

        items.push({
            displayName: `Преобразовать "${currentNodeInfo?.displayName ?? '?'}" в`,
            icon: 'fa-solid fa-text-height',
            children: [
                ...headingItems,
                ...['paragraph', 'codeBlock', 'bulletList', 'orderedList'].map((key) => ({
                    displayName: nodes[key].displayName,
                    icon: nodes[key].icon,
                    shortcut: nodes[key].shortcut,
                    isVisible: !!(editor.value!.schema.nodes[key] && currentNodeInfo?.name !== key),
                    callback: nodes[key].callback,
                })),
            ],
        })

        if (editor.value!.schema.nodes['blockquote']) {
            items.push({
                displayName: nodes['blockquote'].displayName,
                icon: nodes['blockquote'].icon,
                shortcut: nodes['blockquote'].shortcut,
                isActive: editor.value.isActive(nodes['blockquote'].name),
                isVisible: !editor.value.isActive('bulletList') && !editor.value.isActive('orderedList'),
                callback: nodes['blockquote'].callback,
            })
        }
    }

    return items
})

const selectedText = computed(() => {
    const {view, state} = editor.value!
    const {from, to} = view.state.selection
    return state.doc.textBetween(from, to, '')
})

document.addEventListener('scroll', onScroll)

onUnmounted(() => {
    document.removeEventListener('scroll', onScroll)
})

watch(contentModel, (value) => {
    if (currentEditorContent !== value) {
        currentEditorContent = value ?? ''
        editor.value!.commands.setContent(currentEditorContent)
    }
})

function onScroll() {
    linkOverlayPanel.value?.hide()
}

function isAtTextBlock(): boolean {
    return editor.value ? editor.value.state.selection.$anchor.parent.isTextblock : false
}

function isAtEmptyRootParagraph(editorState: EditorState | undefined = undefined): boolean {
    if (!editorState) {
        if (!editor.value) {
            return false
        }

        editorState = editor.value.state
    }

    const {$anchor, empty} = editorState.selection
    const isRootDepth = $anchor.depth === 1
    const isEmptyParagraph = $anchor.parent.type.name === 'paragraph' && !$anchor.parent.type.spec.code && !$anchor.parent.textContent

    return empty && isRootDepth && isEmptyParagraph
}

function getCurrentNodeInfo(): EditorNodeInfo | null {
    if (!editor.value) {
        return null
    }

    for (const key in nodes) {
        if (editor.value.isActive(nodes[key].name, nodes[key].attributes)) {
            return nodes[key]
        }
    }

    return null
}

function onLinkOverlayPanelShow() {
    currentLink.text = selectedText.value
    currentLink.href = editor.value?.getAttributes('link').href
}

function openImageDialog(callback: ((file: File) => void)) {
    const input = document.createElement('input')
    input.type = 'file'
    input.accept = 'image/jpeg, image/png, image/jpg'
    input.style.display = 'none'
    input.onchange = () => {
        callback(input.files![0])
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
    const selection = editor.value!.view!.state.selection

    if (currentLink.href.trim() !== '') {
        editor.value!
            .chain()
            .focus()
            .setLink({href: currentLink.href})
            .insertContentAt({from: selection.from, to: selection.to}, currentLink.text)
            .run()
    }

    linkOverlayPanel.value?.hide()
}

function unsetLink() {
    editor.value!.chain().focus().unsetLink().run()
    linkOverlayPanel.value?.hide()
}
</script>

<template>
    <div>
        <BubbleMenu
            v-if="editor && editable && !withoutMenus && menuItems.length > 0"
            :editor="editor"
            :tippy-options="{zIndex: 100, maxWidth: 'none'}"
            :update-delay="0"
            class="hidden lg:block"
        >
            <EditorHorizontalMenu
                :items="menuItems"
                class="rounded border drop-shadow-[0_0px_2px_rgba(0,0,0,0.3)]"
            />
        </BubbleMenu>

        <FloatingMenu
            v-if="editor && editable && !withoutMenus"
            :editor="editor"
            :tippy-options="{ placement: 'left', offset: [0, 0], zIndex: 100 }"
            :should-show="({state}) => isAtEmptyRootParagraph(state)"
            class="hidden lg:block"
        >
            <Button icon="fa-solid fa-plus" text rounded severity="secondary" @click="addNodeMenu?.toggle"/>
            <EditorVerticalMenu ref="addNodeMenu" title="Добавить" :items="menuItems"/>
        </FloatingMenu>

        <OverlayPanel v-if="editable && !withoutMenus" ref="linkOverlayPanel" @show="onLinkOverlayPanelShow">
            <form class="space-y-2" @submit.prevent="setLink">
                <InputGroup>
                    <InputText v-model="currentLink.href" placeholder="https://" class="w-full" autocomplete="off"/>
                    <Button icon="fa-solid fa-check" outlined title="Сохранить ссылку" type="submit"/>
                    <Button icon="fa-solid fa-xmark" outlined title="Удалить ссылку" severity="danger"
                            @click="unsetLink"/>
                </InputGroup>
                <InputText v-model="currentLink.text" placeholder="Текст..." class="w-full" autocomplete="off"/>
            </form>
        </OverlayPanel>

        <div>
            <EditorContent :editor="editor"/>
        </div>

        <EditorHorizontalMenu
            v-if="editor && editable && !withoutMenus"
            :items="menuItems"
            class="block lg:hidden sticky bottom-0 rounded-b-xl border-t overflow-x-auto whitespace-nowrap"
        />
    </div>
</template>

<style scoped>

</style>
