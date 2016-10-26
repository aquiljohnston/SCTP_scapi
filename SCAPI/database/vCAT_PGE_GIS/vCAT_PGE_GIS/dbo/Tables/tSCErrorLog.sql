CREATE TABLE [dbo].[tSCErrorLog] (
    [SCErrorLogID]     INT           IDENTITY (1, 1) NOT NULL,
    [ProjectID]        INT           NULL,
    [SouceID]          VARCHAR (50)  NULL,
    [UserUID]          VARCHAR (100) NULL,
    [ErrorNumber]      INT           NULL,
    [ErrorDescription] VARCHAR (500) NULL,
    [srcDTLT]          DATETIME      NULL,
    [svrDTLT]          DATETIME      CONSTRAINT [DF_tSCErrorLog_svrDTLT] DEFAULT (getdate()) NULL
);

