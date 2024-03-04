import {ref} from 'vue'

export function useCurrentComponent() {
    const currentComponent = ref( null );
    const setCurrentComponent = ( component ) => {
        currentComponent.value = component;
    };

    return {currentComponent, setCurrentComponent};
}
