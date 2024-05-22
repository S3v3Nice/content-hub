import axios from 'axios'
import {defineStore} from 'pinia'
import {type PostCategory} from '@/types'

interface PostCategoryStore {
    isLoaded: boolean
    categories: PostCategory[]
}

export const usePostCategoryStore = defineStore('postCategory', {
    state: (): PostCategoryStore => ({
        isLoaded: false,
        categories: []
    }),
    actions: {
        async load() {
            await axios.get('/api/post-categories').then(({data}) => {
                this.isLoaded = true
                this.categories = data.records
            }).catch(() => {
            })
        },

        getBySlug(slug: string): PostCategory | undefined {
            return this.categories.find(category => category.slug === slug)
        }
    }
})
