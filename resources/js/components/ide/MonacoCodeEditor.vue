<script setup lang="ts">
import * as monaco from 'monaco-editor';
import { onMounted, onUnmounted, ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue: string;
        originalValue?: string;
        language?: string;
        diffMode?: boolean;
        readOnly?: boolean;
    }>(),
    {
        originalValue: '',
        language: 'php',
        diffMode: false,
        readOnly: false,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const editorContainer = ref<HTMLDivElement | null>(null);

let codeEditor: monaco.editor.IStandaloneCodeEditor | null = null;
let diffEditor: monaco.editor.IStandaloneDiffEditor | null = null;
let originalModel: monaco.editor.ITextModel | null = null;
let modifiedModel: monaco.editor.ITextModel | null = null;
let isSyncingFromProp = false;

function ensureMonacoEnvironment(): void {
    const globalWithMonaco = globalThis as typeof globalThis & {
        MonacoEnvironment?: {
            getWorker: () => Worker;
        };
    };

    if (!globalWithMonaco.MonacoEnvironment) {
        globalWithMonaco.MonacoEnvironment = {
            getWorker: () =>
                new Worker(
                    new URL(
                        '../../../../node_modules/monaco-editor/esm/vs/editor/editor.worker.js',
                        import.meta.url,
                    ),
                    { type: 'module' },
                ),
        };
    }
}

function mapLanguage(lang: string): string {
    switch (lang.toLowerCase()) {
        case 'ts':
        case 'typescript':
            return 'typescript';
        case 'sql':
            return 'sql';
        case 'json':
            return 'json';
        default:
            return 'php';
    }
}

function disposeEditors(): void {
    if (diffEditor !== null) {
        diffEditor.dispose();
        diffEditor = null;
    }

    if (codeEditor !== null) {
        codeEditor.dispose();
        codeEditor = null;
    }

    if (originalModel !== null) {
        originalModel.dispose();
        originalModel = null;
    }

    if (modifiedModel !== null) {
        modifiedModel.dispose();
        modifiedModel = null;
    }
}

function mountEditor(): void {
    disposeEditors();

    if (editorContainer.value === null) {
        return;
    }

    ensureMonacoEnvironment();
    const monacoLang = mapLanguage(props.language);

    if (props.diffMode) {
        originalModel = monaco.editor.createModel(
            props.originalValue,
            monacoLang,
        );
        modifiedModel = monaco.editor.createModel(props.modelValue, monacoLang);

        diffEditor = monaco.editor.createDiffEditor(editorContainer.value, {
            theme: 'vs-dark',
            automaticLayout: true,
            minimap: { enabled: false },
            fontSize: 13,
            scrollBeyondLastLine: false,
            renderSideBySide: true,
            readOnly: props.readOnly,
        });

        diffEditor.setModel({
            original: originalModel,
            modified: modifiedModel,
        });

        modifiedModel.onDidChangeContent(() => {
            if (isSyncingFromProp || modifiedModel === null) {
                return;
            }

            emit('update:modelValue', modifiedModel.getValue());
        });

        return;
    }

    codeEditor = monaco.editor.create(editorContainer.value, {
        value: props.modelValue,
        language: monacoLang,
        theme: 'vs-dark',
        automaticLayout: true,
        minimap: { enabled: false },
        fontSize: 13,
        tabSize: 4,
        folding: true,
        scrollBeyondLastLine: false,
        readOnly: props.readOnly,
        padding: { top: 10, bottom: 10 },
    });

    codeEditor.onDidChangeModelContent(() => {
        if (isSyncingFromProp || codeEditor === null) {
            return;
        }

        emit('update:modelValue', codeEditor.getValue());
    });
}

watch(
    () => props.diffMode,
    () => {
        mountEditor();
    },
);

watch(
    () => props.language,
    (newLanguage) => {
        const monacoLang = mapLanguage(newLanguage);

        if (codeEditor !== null) {
            const model = codeEditor.getModel();

            if (model !== null) {
                monaco.editor.setModelLanguage(model, monacoLang);
            }
        }

        if (originalModel !== null) {
            monaco.editor.setModelLanguage(originalModel, monacoLang);
        }

        if (modifiedModel !== null) {
            monaco.editor.setModelLanguage(modifiedModel, monacoLang);
        }
    },
);

watch(
    () => props.modelValue,
    (newValue) => {
        if (codeEditor !== null && codeEditor.getValue() !== newValue) {
            isSyncingFromProp = true;
            codeEditor.setValue(newValue);
            isSyncingFromProp = false;
        }

        if (modifiedModel !== null && modifiedModel.getValue() !== newValue) {
            isSyncingFromProp = true;
            modifiedModel.setValue(newValue);
            isSyncingFromProp = false;
        }
    },
);

watch(
    () => props.originalValue,
    (newOriginal) => {
        if (
            originalModel !== null &&
            originalModel.getValue() !== newOriginal
        ) {
            originalModel.setValue(newOriginal);
        }
    },
);

onMounted(() => {
    mountEditor();
});

onUnmounted(() => {
    disposeEditors();
});
</script>

<template>
    <div
        ref="editorContainer"
        data-testid="monaco-editor-container"
        class="h-full min-h-[260px] w-full overflow-hidden bg-[#1e1e1e]"
    />
</template>
