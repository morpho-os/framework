type SourceCode = string
type SourceFile = File

type TranslationUnit = {
    program: SourceCode | SourceFile
}