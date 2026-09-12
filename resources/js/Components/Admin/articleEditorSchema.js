import Link from '@tiptap/extension-link';
import TextAlign from '@tiptap/extension-text-align';
import StarterKit from '@tiptap/starter-kit';

export function createArticleEditorExtensions(contract) {
    return [
        StarterKit.configure({
            code: false,
            codeBlock: false,
            dropcursor: false,
            heading: {
                levels: contract.headingLevels,
            },
            horizontalRule: false,
            link: false,
            strike: false,
            underline: false,
        }),
        Link.configure({
            autolink: false,
            defaultProtocol: 'https',
            openOnClick: false,
        }),
        TextAlign.configure({
            alignments: contract.alignments,
            types: ['heading', 'paragraph'],
        }),
    ];
}

export function schemaVocabulary(editor) {
    return {
        marks: Object.keys(editor.schema.marks).sort(),
        nodes: Object.keys(editor.schema.nodes).sort(),
    };
}

export function schemaMatchesContract(editor, contract) {
    const vocabulary = schemaVocabulary(editor);

    return (
        sameList(contract?.marks, vocabulary.marks) &&
        sameList(contract?.nodes, vocabulary.nodes)
    );
}

function sameList(left, right) {
    return JSON.stringify([...(left ?? [])].sort()) === JSON.stringify([...(right ?? [])].sort());
}
