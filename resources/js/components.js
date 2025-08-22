import { registerComponent as registerAccordionComponent } from './components/accordion'
import { registerComponent as registerAlertDialogComponent } from './components/alert-dialog'
import { registerComponent as registerAvatarComponent } from './components/avatar'
import { registerComponent as registerCalendarComponent } from './components/calendar'
import { registerComponent as registerCarouselComponent } from './components/carousel'
import { registerComponent as registerCheckboxComponent } from './components/checkbox'
import { registerComponent as registerCollapsibleComponent } from './components/collapsible'
import { registerComponent as registerDialogComponent } from './components/dialog'
import { registerComponent as registerDrawerComponent } from './components/drawer'
import { registerComponent as registerDropdownMenuComponent } from './components/dropdown-menu'
import { registerComponent as registerHoverCardComponent } from './components/hover-card'
import { registerComponent as registerPopoverComponent } from './components/popover'
import { registerComponent as registerProgressComponent } from './components/progress'
import { registerComponent as registerRadioGroupComponent } from './components/radio-group'
import { registerComponent as registerSelectComponent } from './components/select'
import { registerComponent as registerSliderComponent } from './components/slider'
import { registerComponent as registerSwitchComponent } from './components/switch'
import { registerComponent as registerTabsComponent } from './components/tabs'
import { registerComponent as registerToggleComponent } from './components/toggle'
import { registerComponent as registerToggleGroupComponent } from './components/toggle-group'
import { registerComponent as registerTooltipComponent } from './components/tooltip'

const registerAllComponents = () => {
    registerAccordionComponent()
    registerAlertDialogComponent()
    registerAvatarComponent()
    registerCalendarComponent()
    registerCarouselComponent()
    registerCheckboxComponent()
    registerCollapsibleComponent()
    registerDialogComponent()
    registerDrawerComponent()
    registerDropdownMenuComponent()
    registerHoverCardComponent()
    registerPopoverComponent()
    registerProgressComponent()
    registerRadioGroupComponent()
    registerSelectComponent()
    registerSliderComponent()
    registerSwitchComponent()
    registerTabsComponent()
    registerToggleComponent()
    registerToggleGroupComponent()
    registerTooltipComponent()
}

document.addEventListener('alpine:init', registerAllComponents)
