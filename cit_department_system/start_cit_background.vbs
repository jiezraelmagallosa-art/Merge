Set fso = CreateObject("Scripting.FileSystemObject")
Set wshShell = CreateObject("WScript.Shell")

scriptDir = fso.GetParentFolderName(WScript.ScriptFullName)
batPath = scriptDir & "\start_cit_server_silent.bat"

wshShell.CurrentDirectory = scriptDir
wshShell.Run """" & batPath & """", 0, False
