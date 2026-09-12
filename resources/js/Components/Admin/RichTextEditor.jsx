import { EditorContent, useEditor } from '@tiptap/react';
import { useEffect, useMemo } from 'react';
import { createArticleEditorExtensions, schemaMatchesContract } from './articleEditorSchema';

function ToolbarButton({ active = false, children, disabled = false, onClick }) {
    return (
        <button
            className={`min-h-10 border px-3 font-mono text-xs font-bold uppercase transition disabled:cursor-not-allowed disabled:opacity-50 ${
                active
                    ? 'border-blood bg-blood text-ink-0'
                    : 'border-line-strong text-bone hover:border-blood hover:text-blood'
            }`}
            disabled={disabled}
            onClick={onClick}
            type="button"
        >
            {children}
        </button>
    );
}

export default function RichTextEditor({ bodyContract, error, onChange, value }) {
    const extensions = useMemo(
        () => createArticleEditorExtensions(bodyContract),
        [bodyContract],
    );

    const editor = useEditor({
        content: value,
        extensions,
        immediatelyRender: false,
        onUpdate: ({ editor: currentEditor }) => onChange(currentEditor.getJSON()),
    });

    useEffect(() => {
        if (!editor || !value) return;

        const current = JSON.stringify(editor.getJSON());
        const incoming = JSON.stringify(value);

        if (current !== incoming) {
            editor.commands.setContent(value, { emitUpdate: false });
        }
    }, [editor, value]);

    const setLink = () => {
        if (!editor) return;

        const existing = editor.getAttributes('link').href ?? '';
        const href = window.prompt('Γράψτε τον σύνδεσμο που ξεκινά με http ή https.', existing);

        if (href === null) return;

        const trimmed = href.trim();

        if (trimmed === '') {
            editor.chain().focus().unsetLink().run();
            onChange(editor.getJSON());
            return;
        }

        if (!/^https?:\/\/[^\s]+$/i.test(trimmed)) {
            window.alert('Ο σύνδεσμος πρέπει να ξεκινά με http ή https.');
            return;
        }

        editor.chain().focus().extendMarkRange('link').setLink({ href: trimmed }).run();
        onChange(editor.getJSON());
    };

    const contractOk = editor ? schemaMatchesContract(editor, bodyContract) : true;

    if (!contractOk) {
        return (
            <div className="border border-blood bg-ink-3 p-4 text-sm leading-6 text-blood-deep" role="alert">
                Το πρόγραμμα επεξεργασίας δεν συμφωνεί με τον τρόπο που εμφανίζεται το άρθρο. Σταματήστε εδώ και ζητήστε τεχνικό έλεγχο.
            </div>
        );
    }

    return (
        <div>
            <div className="flex flex-wrap gap-2 border border-line-strong bg-ink-3 p-3" aria-label="Εργαλεία μορφοποίησης">
                <ToolbarButton active={editor?.isActive('bold')} disabled={!editor} onClick={() => editor?.chain().focus().toggleBold().run()}>
                    B
                </ToolbarButton>
                <ToolbarButton active={editor?.isActive('italic')} disabled={!editor} onClick={() => editor?.chain().focus().toggleItalic().run()}>
                    I
                </ToolbarButton>
                <ToolbarButton active={editor?.isActive('heading', { level: 2 })} disabled={!editor} onClick={() => editor?.chain().focus().toggleHeading({ level: 2 }).run()}>
                    H2
                </ToolbarButton>
                <ToolbarButton active={editor?.isActive('heading', { level: 3 })} disabled={!editor} onClick={() => editor?.chain().focus().toggleHeading({ level: 3 }).run()}>
                    H3
                </ToolbarButton>
                <ToolbarButton active={editor?.isActive('bulletList')} disabled={!editor} onClick={() => editor?.chain().focus().toggleBulletList().run()}>
                    Λίστα
                </ToolbarButton>
                <ToolbarButton active={editor?.isActive('orderedList')} disabled={!editor} onClick={() => editor?.chain().focus().toggleOrderedList().run()}>
                    1·2
                </ToolbarButton>
                <ToolbarButton active={editor?.isActive('blockquote')} disabled={!editor} onClick={() => editor?.chain().focus().toggleBlockquote().run()}>
                    Απόσπασμα
                </ToolbarButton>
                <ToolbarButton active={editor?.isActive('link')} disabled={!editor} onClick={setLink}>
                    Link
                </ToolbarButton>
                {bodyContract.alignments.map((alignment) => (
                    <ToolbarButton
                        active={editor?.isActive({ textAlign: alignment })}
                        disabled={!editor}
                        key={alignment}
                        onClick={() => editor?.chain().focus().setTextAlign(alignment).run()}
                    >
                        {alignment}
                    </ToolbarButton>
                ))}
            </div>

            <div className="min-h-[24rem] border border-line-strong bg-ink-1 px-4 py-3 text-base leading-7 text-bone focus-within:border-blood focus-within:ring-2 focus-within:ring-blood-glow">
                <EditorContent editor={editor} />
            </div>

            {error ? (
                <p className="mt-2 text-sm text-blood-deep" role="alert">
                    {error}
                </p>
            ) : null}
        </div>
    );
}
