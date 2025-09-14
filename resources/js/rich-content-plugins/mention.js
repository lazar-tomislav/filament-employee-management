import tippy from 'tippy.js'
import Mention from "@tiptap/extension-mention";

export default Mention.configure({
    HTMLAttributes: {
        class: 'mention',
    },
    suggestion: {
        items: async ({query}) => {
            try {
                // API poziv za dohvaćanje korisnika
                const response = await fetch(`/api/users/search?search=${encodeURIComponent(query)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                })

                if (!response.ok) {
                    throw new Error('Network response was not ok')
                }

                const users = await response.json()
                console.log("users", users);

                return users.slice(0, 5)
            } catch (error) {
                console.error('Error fetching users:', error)
                return [];
            }
        },
        render: () => {
            let component
            let popup

            return {
                onStart: props => {
                    component = new MentionList({
                        props,
                    })

                    if (!props.clientRect) {
                        return
                    }

                    popup = tippy('body', {
                        getReferenceClientRect: props.clientRect,
                        appendTo: () => document.body,
                        content: component.element,
                        showOnCreate: true,
                        interactive: true,
                        trigger: 'manual',
                        placement: 'bottom-start',
                    })
                },

                onUpdate(props) {
                    component.updateProps(props)

                    if (!props.clientRect) {
                        return
                    }

                    popup[0].setProps({
                        getReferenceClientRect: props.clientRect,
                    })
                },

                onKeyDown(props) {
                    if (props.event.key === 'Escape') {
                        popup[0].hide()
                        return true
                    }

                    return component.onKeyDown(props)
                },

                onExit() {
                    popup[0].destroy()
                    component.destroy()
                },
            }
        },
    },
})

class MentionList {
    constructor({props}) {
        this.props = props
        this.selectedIndex = 0

        this.element = document.createElement('div')
        this.element.className = 'mention-dropdown'

        this.render()
    }

    updateProps(props) {
        this.props = props
        this.render()
    }

    render() {
        const items = this.props.items

        if (items.length === 0) {
            this.element.innerHTML = `
                <div class="mention-no-results">
                    <svg class="mention-no-results-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span>Nema pronađenih korisnika</span>
                </div>
            `
            return
        }

        this.element.innerHTML = items
            .map((item, index) => {
                const avatarVariant = `variant-${(index % 5) + 1}`
                const initials = item.label.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()

                return `
                    <div class="mention-item group ${index === this.selectedIndex ? 'selected' : ''}"
                         data-index="${index}"
                         data-selected="${index === this.selectedIndex}">
                        <div class="mention-avatar ${avatarVariant}">
                            ${initials}
                        </div>
                        <div class="mention-user-info">
                            <div class="mention-user-name">${item.label}</div>
                            ${item.email ? `<div class="mention-user-email">${item.email}</div>` : ''}
                        </div>
                    </div>
                `
            })
            .join('')

        // Dodaj event listenere
        this.element.querySelectorAll('.mention-item').forEach((item, index) => {
            item.addEventListener('click', () => {
                this.selectItem(index)
            })

            // Dodaj hover efekte
            item.addEventListener('mouseenter', () => {
                this.selectedIndex = index
                this.updateSelection()
            })
        })
    }

    updateSelection() {
        this.element.querySelectorAll('.mention-item').forEach((item, index) => {
            const isSelected = index === this.selectedIndex
            item.classList.toggle('selected', isSelected)
            item.setAttribute('data-selected', isSelected)
        })
    }

    onKeyDown({event}) {
        if (event.key === 'ArrowUp') {
            this.upHandler()
            return true
        }

        if (event.key === 'ArrowDown') {
            this.downHandler()
            return true
        }

        if (event.key === 'Enter') {
            this.enterHandler()
            return true
        }

        return false
    }

    upHandler() {
        this.selectedIndex = ((this.selectedIndex + this.props.items.length) - 1) % this.props.items.length
        this.updateSelection()
    }

    downHandler() {
        this.selectedIndex = (this.selectedIndex + 1) % this.props.items.length
        this.updateSelection()
    }

    enterHandler() {
        this.selectItem(this.selectedIndex)
    }

    selectItem(index) {
        const item = this.props.items[index]

        if (item) {
            this.props.command({
                id: item.id,
                label: item.label,
            })
        }
    }

    destroy() {
        this.element.remove()
    }
}
